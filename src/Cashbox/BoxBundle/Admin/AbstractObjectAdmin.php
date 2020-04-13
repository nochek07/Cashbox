<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Type\AbstractTypes;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

abstract class AbstractObjectAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';
    protected $listModes = [];

    /**
     * Add Immutable Array to form
     *
     * @param FormMapper $formMapper
     * @param AbstractTypes $type
     * @param array $order
     *
     * @return array
     */
    public function addImmutableArray(FormMapper $formMapper, AbstractTypes $type, array $order = [])
    {
        $result = [
            'order' => $order,
            'map' => [],
            '$choices' => [],
        ];
        foreach ($type::getArrayForAdmin() as $key => $value) {
            $result['choices'][$key] = $key;
            $result['map'][$key] = [$key];
            $result['order'][] = $key;

            $keys = $type::getNewKeys($value);
            if (0 < sizeof($keys)) {
                $formMapper
                    ->add($key, 'sonata_type_immutable_array', [
                        'mapped' => true,
                        'required' => false,
                        'keys' => $keys,
                    ])
                ;
            }
        }
        return $result;
    }
}