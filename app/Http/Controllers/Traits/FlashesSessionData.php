<?php

namespace App\Http\Controllers\Traits;

trait FlashesSessionData
{
    public function flashSuccess(string $message): void
    {
        session()->flash('flash', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function flashError(string $message): void
    {
        session()->flash('flash', [
            'type' => 'error',
            'message' => $message,
        ]);
    }

    public function flashWarning(string $message): void
    {
        session()->flash('flash', [
            'type' => 'warning',
            'message' => $message,
        ]);
    }

    public function flashInfo(string $message): void
    {
        session()->flash('flash', [
            'type' => 'info',
            'message' => $message,
        ]);
    }

    public function flashRedirectBack(string $type, string $message)
    {
        session()->flash('flash', [
            'type' => $type,
            'message' => $message,
        ]);

        return redirect()->back();
    }

    public function flashRedirectTo(string $type, string $message, string $url)
    {
        session()->flash('flash', [
            'type' => $type,
            'message' => $message,
        ]);

        return redirect($url);
    }

    public function flashRedirectIntended(string $type, string $message, string $fallback = '/dashboard')
    {
        session()->flash('flash', [
            'type' => $type,
            'message' => $message,
        ]);

        return redirect()->intended($fallback);
    }

}
