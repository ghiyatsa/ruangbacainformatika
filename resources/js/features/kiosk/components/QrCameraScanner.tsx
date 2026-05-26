import jsQR from 'jsqr';
import { Camera, CameraOff, QrCode } from 'lucide-react';
import {
    forwardRef,
    useEffect,
    useId,
    useImperativeHandle,
    useRef,
    useState,
} from 'react';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface DetectedBarcode {
    rawValue?: string;
}

interface BarcodeDetectorLike {
    detect(image: ImageBitmapSource): Promise<DetectedBarcode[]>;
}

interface BarcodeDetectorConstructor {
    new (options?: { formats?: string[] }): BarcodeDetectorLike;
}

declare global {
    interface Window {
        BarcodeDetector?: BarcodeDetectorConstructor;
    }
}

export interface QrCameraScannerHandle {
    start: () => Promise<void>;
    stop: () => void;
}

export const QrCameraScanner = forwardRef<
    QrCameraScannerHandle,
    {
        onDetected: (value: string) => void;
        className?: string;
    }
>(function QrCameraScanner({ onDetected, className }, ref) {
    const previewId = useId();
    const videoRef = useRef<HTMLVideoElement | null>(null);
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const streamRef = useRef<MediaStream | null>(null);
    const frameRef = useRef<number | null>(null);
    const detectorRef = useRef<BarcodeDetectorLike | null>(null);
    const [isScanning, setIsScanning] = useState(false);
    const [isPreparing, setIsPreparing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const canUseCamera =
        typeof window !== 'undefined' &&
        typeof navigator !== 'undefined' &&
        'mediaDevices' in navigator &&
        typeof navigator.mediaDevices?.getUserMedia === 'function';
    useEffect(() => {
        return () => {
            if (frameRef.current !== null) {
                window.cancelAnimationFrame(frameRef.current);
            }

            streamRef.current?.getTracks().forEach((track) => track.stop());
        };
    }, []);

    const stopScanner = () => {
        if (frameRef.current !== null) {
            window.cancelAnimationFrame(frameRef.current);
            frameRef.current = null;
        }

        streamRef.current?.getTracks().forEach((track) => track.stop());
        streamRef.current = null;

        if (videoRef.current) {
            videoRef.current.srcObject = null;
        }

        setIsScanning(false);
        setIsPreparing(false);
    };

    const startScanner = async () => {
        if (!canUseCamera || isScanning || isPreparing) {
            return;
        }

        setError(null);
        setIsPreparing(true);

        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: {
                        ideal: 'environment',
                    },
                },
                audio: false,
            });

            const Detector = window.BarcodeDetector;

            if (Detector) {
                detectorRef.current = new Detector({
                    formats: ['qr_code'],
                });
            } else {
                detectorRef.current = null;
            }

            streamRef.current = stream;

            const video = videoRef.current;

            if (!video) {
                throw new Error('Pratinjau kamera tidak tersedia.');
            }

            video.srcObject = stream;
            await video.play();

            setIsPreparing(false);
            setIsScanning(true);

            let detectionInFlight = false;

            const scanFrame = async () => {
                if (!videoRef.current) {
                    return;
                }

                if (
                    !detectionInFlight &&
                    videoRef.current.readyState >=
                        HTMLMediaElement.HAVE_CURRENT_DATA
                ) {
                    detectionInFlight = true;

                    try {
                        let detectedValue: string | undefined;

                        if (detectorRef.current) {
                            const detectedCodes =
                                await detectorRef.current.detect(
                                    videoRef.current,
                                );
                            detectedValue = detectedCodes[0]?.rawValue?.trim();
                        } else {
                            const canvas = canvasRef.current;
                            const width = videoRef.current.videoWidth;
                            const height = videoRef.current.videoHeight;

                            if (canvas && width > 0 && height > 0) {
                                canvas.width = width;
                                canvas.height = height;

                                const context = canvas.getContext('2d', {
                                    willReadFrequently: true,
                                });

                                if (context) {
                                    context.drawImage(
                                        videoRef.current,
                                        0,
                                        0,
                                        width,
                                        height,
                                    );

                                    const imageData = context.getImageData(
                                        0,
                                        0,
                                        width,
                                        height,
                                    );
                                    const result = jsQR(
                                        imageData.data,
                                        imageData.width,
                                        imageData.height,
                                    );

                                    detectedValue =
                                        result?.data?.trim() || undefined;
                                }
                            }
                        }

                        if (detectedValue) {
                            onDetected(detectedValue);
                            stopScanner();

                            return;
                        }
                    } catch {
                        setError(
                            'QR belum terbaca. Coba arahkan kamera lebih dekat.',
                        );
                    } finally {
                        detectionInFlight = false;
                    }
                }

                frameRef.current = window.requestAnimationFrame(scanFrame);
            };

            frameRef.current = window.requestAnimationFrame(scanFrame);
        } catch (scanError) {
            stopScanner();

            if (
                scanError instanceof DOMException &&
                scanError.name === 'NotAllowedError'
            ) {
                setError(
                    'Izin kamera ditolak. Gunakan scanner eksternal atau izinkan akses kamera.',
                );

                return;
            }

            setError('Kamera belum bisa digunakan di perangkat ini.');
        }
    };

    useImperativeHandle(ref, () => ({
        start: startScanner,
        stop: stopScanner,
    }));

    return (
        <div className={cn('space-y-3', className)}>
            <div className="flex flex-wrap items-center gap-2">
                <Button
                    type="button"
                    variant={isScanning ? 'secondary' : 'outline'}
                    size="lg"
                    className="h-11"
                    onClick={() => {
                        if (isScanning) {
                            stopScanner();

                            return;
                        }

                        void startScanner();
                    }}
                    disabled={!canUseCamera || isPreparing}
                >
                    {isScanning ? <CameraOff /> : <Camera />}
                    {isPreparing
                        ? 'Menyiapkan Kamera...'
                        : isScanning
                          ? 'Tutup Kamera'
                          : 'Scan dari Kamera'}
                </Button>

                <p className="text-sm text-muted-foreground">
                    Arahkan QR ke kamera.
                </p>
            </div>

            <div className="overflow-hidden rounded-2xl border border-border/70 bg-muted/25">
                <div className="relative aspect-video max-h-64 bg-black">
                    <video
                        id={previewId}
                        ref={videoRef}
                        className={cn(
                            'h-full w-full object-cover transition-opacity',
                            isScanning ? 'opacity-100' : 'opacity-0',
                        )}
                        playsInline
                        muted
                    />

                    {!isScanning ? (
                        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 px-6 text-center text-white/80">
                            <div className="rounded-2xl border border-white/15 bg-white/10 p-3 backdrop-blur-sm">
                                <QrCode className="size-6" />
                            </div>
                            <p className="max-w-xs text-sm leading-6">
                                {canUseCamera
                                    ? 'Buka kamera untuk scan QR langsung dari perangkat kiosk.'
                                    : 'Perangkat ini belum mengizinkan akses kamera.'}
                            </p>
                        </div>
                    ) : (
                        <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
                            <div className="h-36 w-36 rounded-[1.5rem] border-2 border-white/80 shadow-[0_0_0_9999px_rgba(0,0,0,0.28)]" />
                        </div>
                    )}
                </div>
            </div>

            <canvas ref={canvasRef} className="hidden" aria-hidden="true" />
            {error ? <p className="text-sm text-destructive">{error}</p> : null}
        </div>
    );
});
