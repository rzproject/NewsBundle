<?php

namespace Rz\NewsBundle\Model;

abstract class NewsSnapshot implements NewsSnapshotInterface
{

    protected $type;

    protected $path;

    protected $createdAt;

    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $publicationDateStart;

    /**
     * @var \DateTime
     */
    protected $publicationDateEnd;

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }


    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    /**
     * @param \DateTime $publicationDateStart
     */
    public function setPublicationDateStart($publicationDateStart)
    {
        $this->publicationDateStart = $publicationDateStart;
    }

    /**
     * @return \DateTime
     */
    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    /**
     * @param \DateTime $publicationDateEnd
     */
    public function setPublicationDateEnd($publicationDateEnd)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getPath();
    }
}