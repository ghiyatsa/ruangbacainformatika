export interface SkripsiData {
    id: number;
    title: string;
    authorName: string;
    studentId: string;
    year: number | null;
    abstract: string | null;
    keywords: string[];
}

export interface SkripsiShowProps {
    skripsi: {
        data: SkripsiData;
    };
}
