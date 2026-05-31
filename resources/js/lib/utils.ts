import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { InertiaLinkProps } from '@inertiajs/react';
import type { ClassValue } from 'clsx';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function downloadSvgAsPng(svgString: string, filename: string, title?: string) {
    let processedSvg = svgString;
    if (!processedSvg.includes('xmlns=')) {
        processedSvg = processedSvg.replace('<svg', '<svg xmlns="http://www.w3.org/2000/svg"');
    }

    // Substitute CSS variables / currentColor / transparent backgrounds with high-contrast solid colors for PNG download
    processedSvg = processedSvg.replace(/fill="currentColor"/g, 'fill="#432dd7"');
    processedSvg = processedSvg.replace(/fill="transparent"/g, 'fill="#ffffff"');
    processedSvg = processedSvg.replace(/color="currentColor"/g, 'color="#432dd7"');

    const svgBlob = new Blob([processedSvg], { type: 'image/svg+xml;charset=utf-8' });
    const URL = window.URL || window.webkitURL || window;
    const blobUrl = URL.createObjectURL(svgBlob);

    const img = new Image();
    img.onload = () => {
        const canvas = document.createElement('canvas');
        const width = 512;
        const height = title ? 576 : 512; // Extra height for text if title is provided
        
        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        if (ctx) {
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, width, height);

            // Draw QR code and text
            if (title) {
                // Centered slightly higher if there is text at the bottom
                ctx.drawImage(img, 32, 24, 448, 448);
                
                // Draw Title
                ctx.font = 'bold 22px system-ui, -apple-system, sans-serif';
                ctx.fillStyle = '#111827';
                ctx.textAlign = 'center';
                ctx.fillText(title, 256, 508);
                
                // Draw Subtitle
                ctx.font = '500 14px system-ui, -apple-system, sans-serif';
                ctx.fillStyle = '#6b7280';
                ctx.fillText('Ruang Baca Teknik Informatika', 256, 536);
            } else {
                ctx.drawImage(img, 32, 32, 448, 448);
            }
            
            const pngUrl = canvas.toDataURL('image/png');
            const downloadLink = document.createElement('a');
            downloadLink.href = pngUrl;
            downloadLink.download = filename;
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
        URL.revokeObjectURL(blobUrl);
    };
    img.src = blobUrl;
}

