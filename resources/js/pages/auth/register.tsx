import { Form, Head } from '@inertiajs/react';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/RegisteredUserController';
import GoogleIcon from '@/components/common/GoogleIcon';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import TextLink from '@/components/common/TextLink';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { login } from '@/routes';
import { google } from '@/routes/auth';

type Props = {
    canLoginWithGoogle: boolean;
};

export default function Register({ canLoginWithGoogle }: Props) {
    return (
        <>
            <Head title="Register" />

            <div className="flex flex-col gap-6">
                {canLoginWithGoogle ? (
                    <div className="grid gap-3">
                        <a
                            href={google.url()}
                            className={cn(
                                buttonVariants({ variant: 'outline' }),
                                'w-full',
                            )}
                        >
                            <GoogleIcon data-icon="inline-start" />
                            Continue with Google
                        </a>
                    </div>
                ) : null}

                <Form
                    action={store.url()}
                    method="post"
                    disableWhileProcessing
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {canLoginWithGoogle ? (
                                <div className="relative text-center text-xs tracking-[0.2em] text-muted-foreground uppercase">
                                    <span className="relative z-10 bg-card px-3">
                                        Or register with email
                                    </span>
                                    <div className="absolute inset-x-0 top-1/2 border-t border-border" />
                                </div>
                            ) : null}

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Full name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="name"
                                        placeholder="Full Name"
                                        suppressHydrationWarning
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        tabIndex={2}
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                        suppressHydrationWarning
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password</Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        required
                                        tabIndex={3}
                                        autoComplete="new-password"
                                        placeholder="Password"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirm password
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        required
                                        tabIndex={4}
                                        autoComplete="new-password"
                                        placeholder="Confirm Password"
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    tabIndex={5}
                                    disabled={processing}
                                    size={'lg'}
                                >
                                    {processing ? <Spinner /> : null}
                                    Create account
                                </Button>
                            </div>

                            <div className="text-center text-sm text-muted-foreground">
                                Already have an account?{' '}
                                <TextLink href={login()} tabIndex={6}>
                                    Log in
                                </TextLink>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Register.layout = {
    title: 'Create an account',
    description: 'Enter your details below to create your account',
};
