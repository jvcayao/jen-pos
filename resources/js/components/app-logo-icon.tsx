import SunbitesLogo from '../../../public/images/logo/sunbites.png';

interface AppLogoIconProps {
    className?: string;
}

export default function AppLogoIcon({ className }: AppLogoIconProps) {
    return <img src={SunbitesLogo} alt="Sunbites Logo" className={className} />;
}
