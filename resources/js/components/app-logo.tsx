import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square lg:size-42 items-center justify-center rounded-md  ">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>

        </>
    );
}
