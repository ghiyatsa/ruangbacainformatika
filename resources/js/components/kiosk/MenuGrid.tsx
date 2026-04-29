import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { kioskMenuItems } from '@/pages/kiosk/menu';
import type { KioskMenu } from '@/pages/Kiosk/types';

export function MenuGrid({
    onSelect,
}: {
    onSelect: (menu: KioskMenu) => void;
}) {
    return (
        <div className="grid gap-4 sm:grid-cols-2">
            {kioskMenuItems.map((item) => {
                const Icon = item.icon;

                return (
                    <Card key={item.key} size="sm">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Icon className="size-4" />
                                {item.label}
                            </CardTitle>
                            <CardDescription>
                                {item.description}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full"
                                onClick={() => onSelect(item.key)}
                            >
                                Pilih Layanan
                            </Button>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}
