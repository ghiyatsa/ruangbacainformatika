import { Form, Head } from '@inertiajs/react';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/RegisteredUserController';
import GoogleIcon from '@/components/google-icon';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
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
                    {...store.form()}
                    resetOnSuccess={['password', 'password_confirmation']}
                    disableWhileProcessing
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            {canLoginWithGoogle ? (
                                <div className="relative text-center text-xs tracking-[0.2em] text-muted-foreground uppercase">
                                    <span className="relative z-10 bg-card px-3">
                                        Or continue with email
                                    </span>
                                    <div className="absolute inset-x-0 top-1/2 border-t border-border" />
                                </div>
                            ) : null}

                            <div className="grid gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        type="text"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="name"
                                        name="name"
                                        placeholder="Full name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        required
                                        tabIndex={2}
                                        autoComplete="email"
                                        name="email"
                                        placeholder="nama@unimal.ac.id"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password</Label>
                                    <PasswordInput
                                        id="password"
                                        required
                                        tabIndex={4}
                                        autoComplete="new-password"
                                        name="password"
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
                                        required
                                        tabIndex={5}
                                        autoComplete="new-password"
                                        name="password_confirmation"
                                        placeholder="Confirm password"
                                    />
                                    <InputError
                                        message={errors.password_confirmation}
                                    />
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    tabIndex={6}
                                    data-test="register-user-button"
                                    disabled={processing}
                                    size={'lg'}
                                >
                                    {processing ? <Spinner /> : null}
                                    Create account
                                </Button>
                            </div>

                            <div className="text-center text-sm text-muted-foreground">
                                Already have an account?{' '}
                                <TextLink href={login()} tabIndex={7}>
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
    description: 'Enter your email and password below to register.',
};
