export type Session = {
    id: string;
    ip_address: string;
    is_current_device: boolean;
    agent: {
        is_desktop: boolean;
        platform: string;
        browser: string;
    };
    last_active: string;
};

export type SecurityPageProps = {
    sessions?: Session[];
};
