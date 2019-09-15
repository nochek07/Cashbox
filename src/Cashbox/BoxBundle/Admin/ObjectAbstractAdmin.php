<?php

namespace Cashbox\BoxBundle\Admin;

use Cashbox\BoxBundle\Model\Type\TypeAbstract;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

abstract class ObjectAbstractAdmin extends AbstractAdmin
{
    protected $translationDomain = 'BoxBundle';

    protected $listModes = [];

    /**
     * @param FormMapper $formMapper
     * @param TypeAbstract $type
     * @param array $order
     * @return array
     */
    public function addImmutableArray(FormMapper $formMapper, TypeAbstract $type, array $order = [])
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