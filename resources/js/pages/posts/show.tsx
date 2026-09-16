import { PostShowPage } from '@/features/posts/components/PostShowPage';
import type { PostShowPageProps } from '@/features/posts/types';

export default function PostShow(props: PostShowPageProps) {
    return <PostShowPage {...props} />;
}
