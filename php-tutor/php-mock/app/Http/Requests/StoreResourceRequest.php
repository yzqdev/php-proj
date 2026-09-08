<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MockResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 定义资源（含字段 Schema）的校验
 *
 * FormRequest 类比 Spring 的 @Valid + @Validated 校验器，
 * rules() 类似 Bean Validation 的注解约束集中定义
 */
class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],

            'fields' => ['required', 'array', 'min:1'],
            'fields.*.name' => ['required', 'string', 'max:100', 'distinct'],
            // type 必须是 FieldResolver 支持的类型之一，禁止虚构类型
            'fields.*.type' => ['required', 'string', Rule::in(MockResource::FIELD_TYPES)],
            'count' => ['nullable', 'integer', 'min:1', 'max:'.\App\Services\DataGeneratorService::MAX_COUNT],
        ];
    }

    /**
     * enum 的 options 单独用闭包校验（依赖同元素 type，普通 rules 难以表达）
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ((array) $this->input('fields', []) as $i => $field) {
                $type = $field['type'] ?? '';
                $options = $field['options'] ?? null;

                if ($type === 'enum' && (! is_array($options) || $options === [])) {
                    $validator->errors()->add("fields.{$i}.options", 'enum 类型必须提供非空的 options 数组');
                }

                // 同一 Schema 内字段名不可重复（distinct 规则对嵌套数组较脆弱，这里兜底）
                $names = array_column((array) $this->input('fields', []), 'name');
                if (isset($field['name']) && count(array_filter($names, fn ($n) => $n === $field['name'])) > 1) {
                    $validator->errors()->add("fields.{$i}.name", '字段名在 Schema 中必须唯一');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.regex' => '资源名只能包含字母、数字、下划线和连字符',
            'fields.*.type.in' => '不支持的字段类型',
        ];
    }
}
