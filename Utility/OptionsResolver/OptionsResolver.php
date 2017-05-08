<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\OptionsResolver;

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\OptionsResolver\OptionsResolver as BaseOptionsResolver;

/**
 * @deprecated Deprecated in v1.7.x and scheduled for removal in v2.0.x
 */
class OptionsResolver
{
    /**
     * @var array
     */
    private $required = array();

    /**
     * @var array
     */
    private $defaults = array();

    /**
     * @var array
     */
    private $allowedValues = array();

    /**
     * @var array
     */
    private $allowedTypes = array();

    /**
     * @var array
     */
    private $normalizers = array();

    /**
     * @param string $option
     * @param mixed  $value
     */
    public function setDefault($option, $value)
    {
        $this->defaults[$option] = $value;
    }

    /**
     * @param array $options
     */
    public function setRequired(array $options)
    {
        $this->required = $options;
    }

    /**
     * @param string  $option
     * @param mixed[] $values
     *
     * @return $this
     */
    public function setAllowedValues($option, array $values)
    {
        $this->allowedValues[$option] = $values;

        return $this;
    }

    /**
     * @param string  $option
     * @param mixed[] $types
     *
     * @return $this
     */
    public function setAllowedTypes($option, array $types)
    {
        $this->allowedTypes[$option] = $types;

        return $this;
    }

    /**
     * @param string   $option
     * @param \Closure $normalizer
     *
     * @return $this
     */
    public function setNormalizer($option, \Closure $normalizer)
    {
        $this->normalizers[$option] = $normalizer;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function resolve(array $options)
    {
        $resolver = new BaseOptionsResolver();
        $resolver->setDefaults($this->defaults);
        $resolver->setRequired($this->required);

        if (SymfonyFramework::isKernelGreaterThanOrEqualTo(2, 7)) {
            $this->setupResolver($resolver);
        } else {
            $this->setupResolverLegacy($resolver);
        }

        return $resolver->resolve($options);
    }

    /**
     * @param BaseOptionsResolver $resolver
     */
    private function setupResolver(BaseOptionsResolver $resolver)
    {
        foreach ($this->allowedValues as $option => $values) {
            $resolver->setAllowedValues($option, $values);
        }

        foreach ($this->allowedTypes as $option => $types) {
            $resolver->setAllowedTypes($option, $types);
        }

        foreach ($this->normalizers as $option => $normalizer) {
            $resolver->setNormalizer($option, $normalizer);
        }
    }

    /**
     * @param BaseOptionsResolver $resolver
     */
    private function setupResolverLegacy(BaseOptionsResolver $resolver)
    {
        $resolver->setAllowedValues($this->allowedValues);
        $resolver->setAllowedTypes($this->allowedTypes);
        $resolver->setNormalizers($this->normalizers);
    }
}
