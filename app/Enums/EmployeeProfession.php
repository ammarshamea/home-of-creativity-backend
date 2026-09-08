<?php

namespace App\Enums;

enum EmployeeProfession: string
{
    case Sales = 'sales';
    case Design = 'design';
    case Content = 'content';
    case Branding = 'branding';
    case Visualization3d = '3d_visualization';
    case Media = 'media';
    case Web = 'web';
    case Print = 'print';
    case CreativeDirection = 'creative_direction';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
