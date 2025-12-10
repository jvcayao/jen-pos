export interface TwoFactorProps {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

export interface ProfilePageProps {
    mustVerifyEmail: boolean;
    status?: string;
}
