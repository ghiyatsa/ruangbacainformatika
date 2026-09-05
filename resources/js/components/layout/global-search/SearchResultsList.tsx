import { SearchResultItem } from './SearchResultItem';
import type { HistoryItem, QuickResultItem } from './types';

interface SearchResultsListProps {
    books: QuickResultItem[];
    skripsi: QuickResultItem[];
    posts: QuickResultItem[];
    selectedId?: string;
    onSelect: (item: QuickResultItem | HistoryItem) => void;
}

export function SearchResultsList({
    books,
    skripsi,
    posts,
    selectedId,
    onSelect,
}: SearchResultsListProps) {
    return (
        <div className="space-y-4 p-1">
            {books.length > 0 ? (
                <div>
                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                        Buku
                    </p>
                    <div className="space-y-0.5">
                        {books.map((book) => (
                            <SearchResultItem
                                key={`book-${book.id}`}
                                item={book}
                                isSelected={selectedId === `book-${book.id}`}
                                onSelect={onSelect}
                            />
                        ))}
                    </div>
                </div>
            ) : null}

            {skripsi.length > 0 ? (
                <div>
                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                        Karya Ilmiah
                    </p>
                    <div className="space-y-0.5">
                        {skripsi.map((item) => (
                            <SearchResultItem
                                key={`skripsi-${item.id}`}
                                item={item}
                                isSelected={selectedId === `skripsi-${item.id}`}
                                onSelect={onSelect}
                            />
                        ))}
                    </div>
                </div>
            ) : null}

            {posts.length > 0 ? (
                <div>
                    <p className="px-2 py-1 text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                        Artikel
                    </p>
                    <div className="space-y-0.5">
                        {posts.map((post) => (
                            <SearchResultItem
                                key={`post-${post.id}`}
                                item={post}
                                isSelected={selectedId === `post-${post.id}`}
                                onSelect={onSelect}
                            />
                        ))}
                    </div>
                </div>
            ) : null}
        </div>
    );
}
