<?php

namespace Cashbox\BoxBundle\Model\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractTypes
{
    protected static $translationDomain = 'BoxBundle';

    /**
     * Get ArrayForAdmin
     *
     * @return array
     */
    abstract public static function getArrayForAdmin();

    /**
     * Get new keys or array for admin
     *
     * @param array $value
     *
     * @return array
     */
    public static function getNewKeys(array $value)
    {
        foreach ($value as &$children) {
            $children[2]['required'] = false;
        }
        return $value;
    }

    /**
     * Get text validation
     *
     * @param string $type
     * @param array $value
     * @param TranslatorInterface $translator
     *
     * @return string
     */
    public static function getTextValidation(string $type, array $value, TranslatorInterface $translator)
    {
        $textError = "";
        foreach (static::getArrayForAdmin()[$type] as $key => $child) {
            $param = $child[2];
            if ($param['required'] &&
                ((ChoiceType::class == $child[1] && is_null($value[$key])) ||
                (ChoiceType::class != $child[1] && empty(trim($value[$key] ?? ''))))) {
                $field = $translator->trans($param['label'], [], $param['translation_domain']);
                $textError .= "&emsp;Поле \"{$field}\" не заполнено;<br>";
            }
        }
        return $textError;
    }
}