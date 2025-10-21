<?php

namespace App\Http\Resources;

use App\Enums\InputTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'input_type' => $this->input_type->value,
            'input_type_label' => $this->input_type->label(),
        ];
    }
}
