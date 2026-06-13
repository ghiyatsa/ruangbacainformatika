import WelcomePage from '@/features/welcome/components/WelcomePage';
import type { WelcomeProps } from '@/features/welcome/types';

export default function Welcome(props: WelcomeProps) {
    return <WelcomePage {...props} />;
}
