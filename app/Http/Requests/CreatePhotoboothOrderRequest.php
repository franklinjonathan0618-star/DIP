<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreatePhotoboothOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:24', 'regex:/^\+?[0-9][0-9 ()-]{7,20}$/'],
            'layout' => ['required', Rule::in(['strip', 'separate'])],
            'photos' => ['required', 'array', 'list', 'min:1', 'max:3'],
            'photos.*' => ['required', 'string', 'max:1600000'],
            'composite_photo' => ['exclude_unless:layout,strip', 'required', 'string', 'max:2500000'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Data pesanan belum valid.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $dataUrls = $this->input('photos', []);

                if ($this->input('layout') === 'strip') {
                    $dataUrls[] = $this->input('composite_photo');
                }

                $totalBytes = 0;

                foreach ($dataUrls as $index => $dataUrl) {
                    if (! preg_match('/\Adata:image\/jpeg;base64,([A-Za-z0-9+\/]*={0,2})\z/', $dataUrl, $matches)) {
                        $validator->errors()->add("photos.{$index}", 'Foto harus berupa gambar JPEG yang valid.');

                        continue;
                    }

                    $imageContents = base64_decode($matches[1], true);

                    if ($imageContents === false || strlen($imageContents) > 1_000_000) {
                        $validator->errors()->add("photos.{$index}", 'Ukuran setiap foto maksimal 1 MB.');

                        continue;
                    }

                    $imageInfo = getimagesizefromstring($imageContents);

                    if (
                        $imageInfo === false
                        || $imageInfo[2] !== IMAGETYPE_JPEG
                        || $imageInfo[0] > 1200
                        || $imageInfo[1] > 3000
                    ) {
                        $validator->errors()->add("photos.{$index}", 'Ukuran foto tidak valid atau melebihi batas.');

                        continue;
                    }

                    $totalBytes += strlen($imageContents);
                }

                if ($totalBytes > 4_000_000) {
                    $validator->errors()->add('photos', 'Total ukuran foto maksimal 4 MB.');
                }
            },
        ];
    }
}
