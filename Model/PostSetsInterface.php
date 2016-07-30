<?php

namespace Rz\NewsBundle\Model;

interface PostSetsInterface
{
    /**
     * @return mixed
     */
    public function getName();

    /**
     * @param mixed $name
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param mixed $description
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getSettings();

    /**
     * @param mixed $settings
     */
    public function setSettings($settings);

    /**
     * {@inheritDoc}
     */
    public function getSetting($name, $default = null);

    /**
     * {@inheritDoc}
     */
    public function setSetting($name, $value);

    /**
     * @return mixed
     */
    public function getEnabled();

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt);
}
