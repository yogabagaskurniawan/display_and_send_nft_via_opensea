// ConnectButton.js
import { useEffect, useState } from "react";
import { createRoot } from "react-dom/client";
import { useAppKit, useAppKitAccount, createAppKit } from "@reown/appkit/react";
import { bsc, polygon } from '@reown/appkit/networks'
// import { defineChain  } from '@reown/appkit/networks';
import { EthersAdapter } from '@reown/appkit-adapter-ethers'
// **Project ID**
const projectId = import.meta.env.VITE_REOWN_PROJECT_ID;
// **Initialitation AppKit**
const metadata = {
    name: 'Nexarium',
    description: 'The Future of Digital Assets',
    url: 'https://nexarium.io', // origin must match your domain & subdomain
    icons: ['https://avatars.githubusercontent.com/u/179229932']
}

createAppKit({
    adapters: [new EthersAdapter()],
    networks: [polygon],
    projectId,
    metadata,
    includeWalletIds: [
        '20459438007b75f4f4acb98bf29aa3b800550309646d375da5fd4aac6c2a2c66',
    ],
    features: {
        email: false,
        socials: [],
        swaps: false,
        legalCheckbox: true,
    },
    allWallets: "ONLY_MOBILE",
    enableWalletConnect: true,
});

export default function ConnectButton() {
    const { open } = useAppKit();
    const { address, isConnected } = useAppKitAccount();
    const [connected, setConnected] = useState(false);

    useEffect(() => {

        const handleLivewireEvent = () => {
            handleConnect();
        };

        (globalThis as any).Livewire.on("connectWallet", handleLivewireEvent);

    }, []);

    useEffect(() => {
        if (isConnected && address) {
            (globalThis as any).Livewire.dispatch("processWalletLogin", {
                addressData: address,
            });
            setConnected(true);
        }

    }, [isConnected, address]);

    const handleConnect = async () => {
        try {
            console.log("🔗 Attempting to connect wallet...");
            await open({ view: 'Connect' });
        } catch (error) {
            console.error("❌ Error connecting:", error);
        }
    };

    return (
        <span></span>
    );
};

const walletConnect = document.getElementById("walletConnectBtn");
if (walletConnect) {
    const root = createRoot(walletConnect);
    root.render(
        <ConnectButton />
    );
}
