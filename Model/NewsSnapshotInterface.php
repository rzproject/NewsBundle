<?php

namespace Rz\NewsBundle\Model;

interface NewsSnapshotInterface
{
    /**
     * @return mixed
     */
    public function getPath();

    /**
     * @param mixed $path
     */
    public function setPath($path);

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
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt();

    /**
     * @return \DateTime
     */
    public function getPublicationDateStart();

    /**
     * @param \DateTime $publicationDateStart
     */
    public function setPublicationDateStart($publicationDateStart);

    /**
     * @return \DateTime
     */
    public function getPublicationDateEnd();

    /**
     * @param \DateTime $publicationDateEnd
     */
    public function setPublicationDateEnd($publicationDateEnd);

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @param mixed $type
     */
    public function setType($type);
}
