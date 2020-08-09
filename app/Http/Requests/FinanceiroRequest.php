<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FinanceiroRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'tpfinanceiro'  => 'required|in:R,P',
            'vencimentodt'  => 'required',
            'valor'  => 'required|numeric',
            'pessoa'  => 'required',
        ];
    }
}
