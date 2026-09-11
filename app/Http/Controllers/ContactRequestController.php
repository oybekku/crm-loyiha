<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    /**
     * Reklama/spam havolalarining eng ko'p tarqalgan shakllarini ushlaydi:
     * http(s)://, www., yoki so'z + nuqta + domen zonasi (masalan "sayt.uz").
     */
    private const LINK_PATTERN = '/(https?:\/\/|www\.|\b[a-z0-9-]+\.(uz|com|net|org|ru|io|site|xyz|info|biz|me|tv|co)\b)/i';

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'phone'     => ['required', 'string', 'max:30'],
            'message'   => [
                'nullable', 'string', 'max:1000',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if ($value && preg_match(self::LINK_PATTERN, $value)) {
                        $fail('Izohda havola (link) qoldirish mumkin emas.');
                    }
                },
            ],
        ], [
            'full_name.required' => 'Ismingizni kiriting',
            'phone.required'     => 'Telefon raqamingizni kiriting',
        ]);

        ContactRequest::create($data);

        return back()->with('contact_sent', true);
    }
}
