export function SearchFooter() {
    return (
        <div className="flex items-center justify-between border-t bg-muted/40 px-3 py-2 text-[11px] text-muted-foreground">
            <div className="flex items-center gap-3">
                <span className="flex items-center gap-1">
                    <kbd className="rounded border bg-background px-1 py-0.5 font-mono text-[10px]">
                        ↑
                    </kbd>
                    <kbd className="rounded border bg-background px-1 py-0.5 font-mono text-[10px]">
                        ↓
                    </kbd>
                    Pilih
                </span>
                <span className="flex items-center gap-1">
                    <kbd className="rounded border bg-background px-1.5 py-0.5 font-mono text-[10px]">
                        ↵
                    </kbd>
                    Buka
                </span>
            </div>
            <span className="flex items-center gap-1">
                <kbd className="rounded border bg-background px-1 py-0.5 font-mono text-[10px]">
                    Esc
                </kbd>
                Tutup
            </span>
        </div>
    );
}
