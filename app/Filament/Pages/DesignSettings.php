<?php

namespace App\Filament\Pages;

use App\Services\DesignSettingsService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload as FilamentFileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class DesignSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string  $view            = 'filament.pages.design-settings';
    protected static ?string $navigationIcon  = 'heroicon-o-swatch';
    protected static ?string $navigationLabel = 'Dizayn sozlamalari';
    protected static ?string $navigationGroup = 'Sozlamalar';
    protected static ?string $title           = 'Dizayn sozlamalari';
    protected static ?int    $navigationSort  = 10;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(DesignSettingsService::get());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                // ── Kirish sahifasi foni ───────────────────────────────────
                Section::make('Kirish sahifasi (Login) foni')
                    ->icon('heroicon-o-photo')
                    ->description('Login sahifasiga fon rasmi qo\'ying. Rasmni yuklash uchun "Rasm yuklash" tugmasini bosing.')
                    ->columns(2)
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('login_bg_preview')
                            ->label('Joriy rasm')
                            ->content(function () {
                                $path = \App\Services\DesignSettingsService::get()['login_bg_image'] ?? '';
                                if (!$path) return new \Illuminate\Support\HtmlString('<span style="color:#9ca3af;font-size:13px">Rasm yuklanmagan</span>');
                                $url = asset('storage/' . $path);
                                return new \Illuminate\Support\HtmlString(
                                    "<img src=\"{$url}\" style=\"max-height:160px;border-radius:8px;border:1px solid #e5e7eb;object-fit:cover\" />"
                                );
                            })
                            ->columnSpanFull(),

                        TextInput::make('login_bg_opacity')
                            ->label('Rasm shaffofligi')
                            ->numeric()->minValue(10)->maxValue(100)->suffix('%')
                            ->helperText('100 = to\'liq ko\'rinadi, 30 = xira'),

                        Select::make('login_card_blur')
                            ->label('Login kartasi foni')
                            ->options([
                                'white'  => 'Oq (to\'liq)',
                                'glass'  => 'Shisha effekti (glassmorphism)',
                                'none'   => 'Shaffof',
                            ]),
                    ]),

                // ── Sidebar Light ──────────────────────────────────────────
                Section::make('Sidebar — Kunduz rejimi (Light)')
                    ->icon('heroicon-o-sun')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('sidebar_color')
                            ->label('Fon rangi'),

                        TextInput::make('sidebar_opacity')
                            ->label('Shaffoflik')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                        ColorPicker::make('sidebar_text_color')
                            ->label('Matn rangi'),

                        ColorPicker::make('sidebar_active_color')
                            ->label('Faol element rangi')
                            ->columnSpan(1),
                    ]),

                // ── Sidebar Dark ───────────────────────────────────────────
                Section::make('Sidebar — Tungi rejim (Dark)')
                    ->icon('heroicon-o-moon')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('sidebar_dark_color')
                            ->label('Fon rangi'),

                        TextInput::make('sidebar_dark_opacity')
                            ->label('Shaffoflik')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                        ColorPicker::make('sidebar_dark_text_color')
                            ->label('Matn rangi'),

                        ColorPicker::make('sidebar_dark_active_color')
                            ->label('Faol element rangi')
                            ->columnSpan(1),
                    ]),

                // ── Header ─────────────────────────────────────────────────
                Section::make('Header (yuqori qator)')
                    ->icon('heroicon-o-bars-3')
                    ->columns(3)
                    ->schema([
                        ColorPicker::make('header_color')
                            ->label('Fon rangi'),

                        TextInput::make('header_opacity')
                            ->label('Shaffoflik')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%'),

                        ColorPicker::make('header_text_color')
                            ->label('Matn / ikonalar rangi'),
                    ]),

                // ── Body Light ─────────────────────────────────────────────
                Section::make('Asosiy fon — Kunduz rejimi (Light)')
                    ->icon('heroicon-o-computer-desktop')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('light_mode_bg')
                            ->label('Fon rangi'),

                        ColorPicker::make('light_mode_text_color')
                            ->label('Matn rangi'),
                    ]),

                // ── Body Dark ──────────────────────────────────────────────
                Section::make('Asosiy fon — Tungi rejim (Dark)')
                    ->icon('heroicon-o-moon')
                    ->columns(2)
                    ->schema([
                        ColorPicker::make('dark_mode_bg')
                            ->label('Fon rangi'),

                        ColorPicker::make('dark_mode_text_color')
                            ->label('Matn rangi'),
                    ]),

                // ── Kanban ustunlari ───────────────────────────────────────
                Section::make('Kanban — Ustun sarlavhalari')
                    ->icon('heroicon-o-view-columns')
                    ->description('Har bir ustun sarlavhasining fon, shaffoflik va matn rangini sozlang')
                    ->schema(function () {
                        $statuses = \App\Models\ProjectStatus::allOrdered();
                        $rows = [];
                        foreach ($statuses as $ps) {
                            $rows[] = \Filament\Forms\Components\Fieldset::make($ps->label)
                                ->columns(3)
                                ->schema([
                                    ColorPicker::make("kanban_col_{$ps->key}_bg")
                                        ->label('Fon rangi'),
                                    TextInput::make("kanban_col_{$ps->key}_opacity")
                                        ->label('Shaffoflik')
                                        ->numeric()->minValue(0)->maxValue(100)->suffix('%'),
                                    ColorPicker::make("kanban_col_{$ps->key}_text")
                                        ->label('Matn rangi'),
                                ]);
                        }
                        return $rows;
                    }),

                // ── Fon animatsiyasi ──────────────────────────────────────────
                Section::make('Dashboard — Fon animatsiyasi')
                    ->icon('heroicon-o-video-camera')
                    ->description("Bosh sahifa hero qismiga video, Lottie yoki CSS animatsiya qo'ying")
                    ->columns(2)
                    ->schema([
                        Select::make('hero_anim_type')
                            ->label('Animatsiya turi')
                            ->options([
                                'none'   => "Yo'q (animatsiya yo'q)",
                                'video'  => 'Video (MP4 / WebM)',
                                'lottie' => 'Lottie JSON animatsiya',
                                'css'    => 'CSS animatsiya',
                            ])
                            ->live()
                            ->columnSpanFull(),

                        TextInput::make('hero_anim_video_url')
                            ->label("Video yo'li yoki URL (MP4 / WebM)")
                            ->placeholder('/videos/hero-bg.webm  yoki  https://...')
                            ->suffixIcon('heroicon-o-film')
                            ->columnSpanFull()
                            ->hidden(fn($get) => $get('hero_anim_type') !== 'video'),

                        TextInput::make('hero_anim_lottie_url')
                            ->label('Lottie JSON URL')
                            ->placeholder('https://lottie.host/...')
                            ->suffixIcon('heroicon-o-link')
                            ->columnSpanFull()
                            ->hidden(fn($get) => $get('hero_anim_type') !== 'lottie'),

                        Textarea::make('hero_anim_css_code')
                            ->label('CSS kodi (@keyframes + .bh-css-anim yoki boshqa selektor)')
                            ->placeholder("@keyframes float {\n  0%,100% { transform: translateY(0); }\n  50% { transform: translateY(-20px); }\n}\n.bh-css-anim {\n  background: radial-gradient(ellipse at center, #3b82f640 0%, transparent 70%);\n  animation: float 6s ease-in-out infinite;\n}")
                            ->rows(8)
                            ->columnSpanFull()
                            ->hidden(fn($get) => $get('hero_anim_type') !== 'css'),

                        TextInput::make('hero_anim_opacity')
                            ->label('Shaffoflik')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                            ->hidden(fn($get) => ($get('hero_anim_type') ?? 'none') === 'none'),

                        Select::make('hero_anim_position')
                            ->label('Joylashuvi')
                            ->options([
                                'right-half' => "O'ng — yarmi ko'rinadi (standart)",
                                'full'       => "To'liq fon",
                                'right'      => "O'ng — to'liq",
                                'left'       => 'Chap',
                            ])
                            ->hidden(fn($get) => ($get('hero_anim_type') ?? 'none') === 'none'),

                        TextInput::make('hero_anim_speed')
                            ->label('Tezlik')
                            ->numeric()->minValue(0.1)->maxValue(10)->step(0.1)->suffix('x')
                            ->helperText('0.1 = sekin, 2 = 2x tez')
                            ->hidden(fn($get) => $get('hero_anim_type') !== 'lottie'),
                    ]),

                // ── Sidebar Animatsiya ──────────────────────────────────────
                Section::make('Sidebar — Animatsiya sozlamalari')
                    ->icon('heroicon-o-film')
                    ->description("lottiefiles.com dan src= URL ni kiriting. Bo'sh qolsa — standart SVG uy animatsiyasi ishlaydi.")
                    ->columns(4)
                    ->schema([
                        TextInput::make('sidebar_lottie_url')
                            ->label('Lottie URL')
                            ->placeholder('https://lottie.host/...')
                            ->suffixIcon('heroicon-o-link')
                            ->columnSpanFull(),

                        TextInput::make('sidebar_anim_opacity')
                            ->label('Shaffoflik')
                            ->numeric()->minValue(0)->maxValue(100)->suffix('%')
                            ->helperText('0–100'),

                        TextInput::make('sidebar_anim_scale')
                            ->label('Mashtab')
                            ->numeric()->minValue(30)->maxValue(300)->suffix('%')
                            ->helperText('30–300'),

                        TextInput::make('sidebar_anim_speed')
                            ->label('Tezlik')
                            ->numeric()->minValue(0.1)->maxValue(10)->step(0.1)->suffix('x')
                            ->helperText('0.1 = sekin, 2 = 2x tez'),

                        TextInput::make('sidebar_anim_loop')
                            ->label('Takrorlanish')
                            ->numeric()->minValue(0)->maxValue(999)
                            ->helperText('0 = cheksiz'),
                    ]),

            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('uploadLoginBg')
                ->label('Rasm yuklash')
                ->icon('heroicon-o-photo')
                ->color('info')
                ->form([
                    FilamentFileUpload::make('image')
                        ->label('Login sahifasi fon rasmi (JPG, PNG, WebP)')
                        ->image()
                        ->disk('public')
                        ->directory('login-bg')
                        ->imagePreviewHeight('200')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $settings = DesignSettingsService::get();

                    // Eski rasmni o'chirish
                    if (!empty($settings['login_bg_image'])) {
                        Storage::disk('public')->delete($settings['login_bg_image']);
                    }

                    $settings['login_bg_image'] = $data['image'];
                    DesignSettingsService::save($settings);

                    Notification::make()
                        ->title('Rasm saqlandi!')
                        ->body('Login sahifasini yangilang — rasm ko\'rinadi.')
                        ->success()
                        ->send();
                }),

            Action::make('removeLoginBg')
                ->label('Rasmni o\'chirish')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => !empty(DesignSettingsService::get()['login_bg_image']))
                ->action(function (): void {
                    $settings = DesignSettingsService::get();
                    if (!empty($settings['login_bg_image'])) {
                        Storage::disk('public')->delete($settings['login_bg_image']);
                    }
                    $settings['login_bg_image'] = '';
                    DesignSettingsService::save($settings);

                    Notification::make()
                        ->title('Rasm o\'chirildi!')
                        ->warning()
                        ->send();
                }),
        ];
    }

    // FileUpload sahifa yangilanganida null qaytaradi — mavjud qiymatni saqlaymiz
    private const FILE_FIELDS = ['login_bg_image'];

    public function save(): void
    {
        $state    = $this->form->getState();
        $existing = DesignSettingsService::get();

        foreach (self::FILE_FIELDS as $field) {
            if (empty($state[$field]) && !empty($existing[$field])) {
                $state[$field] = $existing[$field];
            }
        }

        DesignSettingsService::save($state);

        Notification::make()
            ->title('Sozlamalar saqlandi!')
            ->body('Sahifani yangilang (F5) — ozgarishlar kuchga kiradi.')
            ->success()
            ->send();
    }

    public function resetToDefaults(): void
    {
        DesignSettingsService::save(DesignSettingsService::defaults());
        $this->form->fill(DesignSettingsService::defaults());

        Notification::make()
            ->title('Standart sozlamalar tiklandi!')
            ->warning()
            ->send();
    }
}
