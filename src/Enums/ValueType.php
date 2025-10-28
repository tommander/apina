<?php

declare(strict_types=1);

namespace Apina\Enums;

enum ValueType: string
{
    case V_STRING = 'string';
    case V_INT = 'int';
    case V_FLOAT = 'float';
    case V_BOOL = 'bool';
    case V_ARRAY = 'array';
    case V_NULL = 'null';
    case V_STRING_HREF = 'href'; // '/gallery/1'
    case V_ARRAY_HREFLIST = 'hreflist'; // ['/gallery/1', '/gallery/2', ...]
}
