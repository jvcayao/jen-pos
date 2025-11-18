import { ImgHTMLAttributes } from "react";
import SunbitesLoginLogo from "../../../public/images/logo/sunbites_logo.png"; // adjust path based on your setup

export default function AppLoginLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return <img src={SunbitesLoginLogo} alt="Sunbites Logo" />;
}
