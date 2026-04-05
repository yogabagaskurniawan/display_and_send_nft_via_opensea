// DisconnectButton.js
import React, { useState, useEffect } from "react";
import { createRoot } from "react-dom/client";
import { useDisconnect, useAppKitAccount, useAppKitProvider, type Provider } from "@reown/appkit/react";
import { BrowserProvider, formatEther, Contract } from 'ethers';

export default function DisconnectButton() {
    const { disconnect } = useDisconnect();
    const { address, isConnected, status } = useAppKitAccount();

    useEffect(() => {
        if (status === 'disconnected') {
            window.location.href = "/logout";
        }
    }, [status]);



    useEffect(() => {
        const handleLivewireEvent = () => {
            console.log('asd');
            
            handleDisconnect();
        };
        (globalThis as any).Livewire.on("disconnect-wallet", handleLivewireEvent);
    }, []);


    const handleDisconnect = async () => {
        try {

            await disconnect();
            window.location.href = "/logout";

        } catch (error) {
            console.error("❌ Error disconnecting:", error);
        }
    };

    return (
        <span></span>
    );
};


const rootElement = document.getElementById("disconnectBtn");
if (rootElement) {
    const root = createRoot(rootElement);
    root.render(
        <DisconnectButton />
    );
}