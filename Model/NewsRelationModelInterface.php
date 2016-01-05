<?php

namespace Rz\NewsBundle\Model;


interface NewsRelationModelInterface
{
    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt();

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled);

    /**
     * {@inheritdoc}
     */
    public function getEnabled();

    /**
     * {@inheritdoc}
     */
    public function setPosition($position);

    /**
     * {@inheritdoc}
     */
    public function getPosition();

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt();
}