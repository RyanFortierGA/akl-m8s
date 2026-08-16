<?php

namespace App\Http\Controllers;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ContactCardController extends Controller
{
    public function show(string $token): InertiaResponse
    {
        $user = User::query()->where('contact_token', $token)->firstOrFail();
        $url = route('cards.show', $token);

        return Inertia::render('cards/Show', [
            'person' => $user->publicCard(),
            'qr' => $this->qrSvg($url),
            'vcardUrl' => route('cards.vcard', $token),
        ]);
    }

    public function vcard(string $token): Response
    {
        $user = User::query()->where('contact_token', $token)->firstOrFail();
        $instagram = $user->instagramHandle();

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:'.$user->name,
            'N:'.$user->name.';;;;',
        ];

        if ($user->phone) {
            $lines[] = 'TEL;TYPE=CELL:'.$user->phone;
        }

        if ($user->email) {
            $lines[] = 'EMAIL:'.$user->email;
        }

        if ($instagram) {
            $lines[] = 'URL:https://instagram.com/'.$instagram;
        }

        $lines[] = 'NOTE:Met on Auckland M8s';
        $lines[] = 'END:VCARD';

        return response(implode("\r\n", $lines))
            ->header('Content-Type', 'text/vcard')
            ->header('Content-Disposition', 'attachment; filename="'.$user->name.'.vcf"');
    }

    private function qrSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(240, 1),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($url);
    }
}
