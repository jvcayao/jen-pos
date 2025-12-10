import SunbitesLoginLogo from '../../../public/images/logo/sunbites_logo.png';

interface AppLoginLogoIconProps {
    className?: string;
}

export default function AppLoginLogoIcon({ className }: AppLoginLogoIconProps) {
    return <img src={SunbitesLoginLogo} alt="Sunbites Logo" className={className} />;
}
