import { Head } from '@inertiajs/react';
import { Mail, MapPin, Phone } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export function ContactPage() {
    return (
        <>
            <Head title="Contact Us - Ruang Baca" />
            <div className="container mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="mb-10 text-center">
                    <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Contact Us
                    </h1>
                    <p className="mx-auto mt-4 max-w-2xl text-lg text-muted-foreground">
                        Have a question about our collections or need
                        assistance? Reach out to us and we'll be happy to help.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                    <Card className="h-full">
                        <CardHeader>
                            <CardTitle>Send us a message</CardTitle>
                            <CardDescription>
                                Fill out the form below and we'll get back to
                                you as soon as possible.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form
                                className="space-y-4"
                                onSubmit={(e) => e.preventDefault()}
                            >
                                <div className="space-y-2">
                                    <Label htmlFor="name">Full Name</Label>
                                    <Input
                                        id="name"
                                        placeholder="Enter your full name"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email Address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        placeholder="Enter your email address"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="subject">Subject</Label>
                                    <Input
                                        id="subject"
                                        placeholder="What is this regarding?"
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="message">Message</Label>
                                    <Textarea
                                        id="message"
                                        placeholder="Type your message here..."
                                        className="min-h-[120px]"
                                    />
                                </div>
                                <Button type="submit" className="w-full">
                                    Send Message
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <div className="space-y-6">
                        <Card>
                            <CardContent className="flex items-start gap-4 p-6">
                                <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                    <MapPin className="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 className="mb-1 text-lg font-semibold text-foreground">
                                        Our Location
                                    </h3>
                                    <p className="text-muted-foreground">
                                        Kampus Bukit Indah
                                        <br />
                                        Program Studi Teknik Informatika
                                        <br />
                                        Universitas Malikussaleh
                                        <br />
                                        Lhokseumawe, Aceh
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent className="flex items-start gap-4 p-6">
                                <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                    <Mail className="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 className="mb-1 text-lg font-semibold text-foreground">
                                        Email Us
                                    </h3>
                                    <p className="text-muted-foreground">
                                        info.tif@unimal.ac.id
                                        <br />
                                        ruangbaca.tif@unimal.ac.id
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardContent className="flex items-start gap-4 p-6">
                                <div className="shrink-0 rounded-full bg-primary/10 p-3 text-primary">
                                    <Phone className="h-6 w-6" />
                                </div>
                                <div>
                                    <h3 className="mb-1 text-lg font-semibold text-foreground">
                                        Call Us
                                    </h3>
                                    <p className="text-muted-foreground">
                                        +62 123 4567 8900
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </>
    );
}
