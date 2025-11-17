import { ImgHTMLAttributes } from "react";
import SunbitesLogo from "../../../public/images/logo/sunbites.png"; // adjust path based on your setup

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return <img src={SunbitesLogo} alt="Sunbites Logo" bg-none />;
}
