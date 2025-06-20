<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BasePage;
use Illuminate\Contracts\Support\Htmlable;
use DiogoGPinto\AuthUIEnhancer\Pages\Auth\Concerns\HasCustomLayout;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\ValidationException;

class Login extends BasePage
{

    use HasCustomLayout;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            // 'email' => 'superadmin@starter-kit.com',
            // 'password' => env('DEFAULT_USER_PASSWORD', '12345678'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // $this->getEmailFormComponent()->label('Email'),
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    } 

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            // 'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            // 'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }
}
