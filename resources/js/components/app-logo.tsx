import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square items-center justify-center rounded-md lg:size-42">
                <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            </div>
        </>
    );
}
