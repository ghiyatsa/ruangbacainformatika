import { Form, Head } from '@inertiajs/react';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import GoogleIcon from '@/components/common/GoogleIcon';
import InputError from '@/components/common/InputError';
import PasswordInput from '@/components/common/PasswordInput';
import TextLink from '@/components/common/TextLink';
import { Button, buttonVariants } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { register } from '@/routes';
import { google } from '@/routes/auth';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
    canLoginWithGoogle: boolean;
};

export default function Login({
    status,
    canResetPassword,
    canRegister,
    canLoginWithGoogle,
}: Props) {
    return (
        <>
            <Head title="Log in" />

            <div className="flex flex-col gap-6">
                {status ? (
                    <div className="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-center text-sm font-medium text-emerald-700">
                        {status}
                    </div>
                ) : null}

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
                    resetOnSuccess={['password']}
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
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <div className="flex items-center">
                                        <Label htmlFor="password">
                                            Password
                                        </Label>
                                        {canResetPassword ? (
                                            <TextLink
                                                href={request()}
                                                className="ml-auto text-sm"
                                                tabIndex={5}
                                            >
                                                Forgot password?
                                            </TextLink>
                                        ) : null}
                                    </div>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                        placeholder="Password"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="flex items-center gap-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        value="1"
                                        tabIndex={3}
                                    />
                                    <Label htmlFor="remember">
                                        Remember me
                                    </Label>
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                    size={'lg'}
                                >
                                    {processing ? <Spinner /> : null}
                                    Log in
                                </Button>
                            </div>

                            {canRegister ? (
                                <div className="text-center text-sm text-muted-foreground">
                                    Don&apos;t have an account?{' '}
                                    <TextLink href={register()} tabIndex={5}>
                                        Register
                                    </TextLink>
                                </div>
                            ) : null}
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

Login.layout = {
    title: 'Log in to your account',
    description: 'Enter your email and password below to log in',
};
