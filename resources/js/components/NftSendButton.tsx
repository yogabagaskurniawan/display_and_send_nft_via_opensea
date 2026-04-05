import { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import { ethers } from "ethers";
import { useAppKit, useAppKitAccount } from "@reown/appkit/react";

const ERC1155_ABI = [
    "function balanceOf(address account, uint256 id) view returns (uint256)",
    "function safeTransferFrom(address from, address to, uint256 id, uint256 amount, bytes data)"
];

function NftSendButton({ contractAddress, tokenId }) {
    const { open } = useAppKit();
    const { address, isConnected } = useAppKitAccount();

    const [balance, setBalance] = useState(0);
    const [toAddress, setToAddress] = useState("");
    const [amount, setAmount] = useState(1);
    const [loading, setLoading] = useState(false);
    const [checking, setChecking] = useState(false);
    const [txHash, setTxHash] = useState("");

    useEffect(() => {
        if (isConnected && address) {
            loadBalance();
        } else {
            setBalance(0);
        }
    }, [isConnected, address, tokenId]);

    const loadBalance = async () => {
        try {
            setChecking(true);

            if (!window.ethereum) return;

            const provider = new ethers.BrowserProvider(window.ethereum);
            const contract = new ethers.Contract(contractAddress, ERC1155_ABI, provider);

            const bal = await contract.balanceOf(address, tokenId);
            setBalance(Number(bal));
        } catch (error) {
            console.error("❌ Gagal cek balance NFT:", error);
            setBalance(0);
        } finally {
            setChecking(false);
        }
    };

    const connectWallet = async () => {
        try {
            await open({ view: "Connect" });
        } catch (error) {
            console.error("❌ Gagal connect wallet:", error);
        }
    };

    const handleSend = async () => {
        try {
            if (!isConnected || !address) {
                alert("Connect wallet dulu");
                return;
            }

            if (!ethers.isAddress(toAddress)) {
                alert("Alamat tujuan tidak valid");
                return;
            }

            if (Number(amount) < 1) {
                alert("Jumlah minimal 1");
                return;
            }

            if (Number(amount) > balance) {
                alert("Jumlah melebihi balance NFT");
                return;
            }

            setLoading(true);
            setTxHash("");

            const provider = new ethers.BrowserProvider(window.ethereum);
            const signer = await provider.getSigner();

            // pastikan chain Polygon
            const network = await provider.getNetwork();
            if (Number(network.chainId) !== 137) {
                await window.ethereum.request({
                    method: "wallet_switchEthereumChain",
                    params: [{ chainId: "0x89" }]
                });
            }

            const contract = new ethers.Contract(contractAddress, ERC1155_ABI, signer);

            const tx = await contract.safeTransferFrom(
                address,
                toAddress,
                tokenId,
                amount,
                "0x"
            );

            const receipt = await tx.wait();
            setTxHash(receipt.hash);

            setToAddress("");
            setAmount(1);
            await loadBalance();

            // kirim ke Livewire kalau mau simpan histori
            if ((globalThis).Livewire) {
                (globalThis).Livewire.dispatch("nftTransferSuccess", {
                    txHash: receipt.hash,
                    from: address,
                    to: toAddress,
                    contract: contractAddress,
                    tokenId: tokenId.toString(),
                    amount: amount.toString(),
                    chain: "polygon"
                });
            }

        } catch (error) {
            console.error("❌ Transfer gagal:", error);

            if (error?.reason) {
                alert("Transfer gagal: " + error.reason);
            } else if (error?.shortMessage) {
                alert("Transfer gagal: " + error.shortMessage);
            } else {
                alert("Transfer gagal");
            }
        } finally {
            setLoading(false);
        }
    };

    // belum connect wallet
    if (!isConnected) {
        return (
            <button
                onClick={connectWallet}
                className="block mt-3 w-full text-center text-sm bg-green-600 text-white py-2 rounded-lg hover:bg-green-700"
            >
                Connect Wallet untuk Send
            </button>
        );
    }

    // sedang cek balance
    if (checking) {
        return (
            <div className="mt-3 text-xs text-gray-500">
                Mengecek kepemilikan NFT...
            </div>
        );
    }

    // wallet connect tapi tidak punya NFT ini
    if (balance <= 0) {
        return (
            <div className="mt-3 text-xs text-red-500 border border-red-200 rounded-lg p-2">
                Wallet ini tidak memiliki NFT ini.
            </div>
        );
    }

    return (
        <div className="mt-4 p-3 border rounded-xl bg-gray-50 space-y-3">
            <p className="text-sm text-gray-700">
                <strong>Balance Anda:</strong> {balance}
            </p>

            <div>
                <label className="block text-xs text-gray-600 mb-1">Wallet Tujuan</label>
                <input
                    type="text"
                    value={toAddress}
                    onChange={(e) => setToAddress(e.target.value)}
                    placeholder="0x..."
                    className="w-full border rounded-lg px-3 py-2 text-sm"
                />
            </div>

            <div>
                <label className="block text-xs text-gray-600 mb-1">Jumlah</label>
                <input
                    type="number"
                    min="1"
                    max={balance}
                    value={amount}
                    onChange={(e) => setAmount(Number(e.target.value))}
                    className="w-full border rounded-lg px-3 py-2 text-sm"
                />
            </div>

            <button
                onClick={handleSend}
                disabled={loading}
                className="w-full text-center text-sm bg-purple-600 text-white py-2 rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
                {loading ? "Sending..." : "Send NFT"}
            </button>

            {txHash && (
                <div className="text-xs text-green-600 break-all">
                    TX: {txHash}
                </div>
            )}
        </div>
    );
}

// auto mount semua widget
document.querySelectorAll(".nft-send-widget").forEach((el) => {
    const contractAddress = el.dataset.contract;
    const tokenId = el.dataset.tokenId;

    const root = createRoot(el);
    root.render(
        <NftSendButton
            contractAddress={contractAddress}
            tokenId={tokenId}
        />
    );
});

export default NftSendButton;