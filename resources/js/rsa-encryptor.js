/**
 * RSA Password Encryptor using Web Crypto API
 * Compatible with phpseclib RSA-OAEP + SHA-256
 */

function pemToArrayBuffer(pem) {
    const pemHeader = '-----BEGIN PUBLIC KEY-----';
    const pemFooter = '-----END PUBLIC KEY-----';
    const pemContents = pem
        .replace(pemHeader, '')
        .replace(pemFooter, '')
        .replace(/\s/g, '');

    const binaryDer = window.atob(pemContents);
    const buffer = new ArrayBuffer(binaryDer.length);
    const view = new Uint8Array(buffer);
    for (let i = 0; i < binaryDer.length; i++) {
        view[i] = binaryDer.charCodeAt(i);
    }
    return buffer;
}

async function importPublicKey(pem) {
    const binaryDer = pemToArrayBuffer(pem);
    return await crypto.subtle.importKey(
        'spki',
        binaryDer,
        { name: 'RSA-OAEP', hash: 'SHA-256' },
        true,
        ['encrypt']
    );
}

async function encryptWithPublicKey(publicKey, text) {
    const encoder = new TextEncoder();
    const data = encoder.encode(text);
    const encrypted = await crypto.subtle.encrypt(
        { name: 'RSA-OAEP' },
        publicKey,
        data
    );
    return btoa(String.fromCharCode(...new Uint8Array(encrypted)));
}

export class RsaEncryptor {
    constructor() {
        this.publicKey = null;
        this.keyId = null;
        this.expiresAt = null;
    }

    async loadKey() {
        // Use cached key if still valid (with 30s buffer)
        if (this.publicKey && this.keyId && this.expiresAt) {
            const expires = new Date(this.expiresAt).getTime();
            if (Date.now() < expires - 30000) {
                return { keyId: this.keyId, publicKey: this.publicKey };
            }
        }

        const response = await fetch('/auth/public-key');
        if (!response.ok) {
            throw new Error('Failed to fetch public key');
        }

        const data = await response.json();
        this.keyId = data.key_id;
        this.expiresAt = data.expires_at;
        this.publicKey = await importPublicKey(data.public_key);

        return { keyId: this.keyId, publicKey: this.publicKey };
    }

    async encryptPassword(password) {
        const { keyId } = await this.loadKey();

        const payload = JSON.stringify({
            pwd: password,
            kid: keyId,
            ts: Math.floor(Date.now() / 1000),
        });

        const encrypted = await encryptWithPublicKey(this.publicKey, payload);
        return { encrypted, keyId };
    }
}

// Global instance for Alpine.js
window.RsaEncryptor = RsaEncryptor;
