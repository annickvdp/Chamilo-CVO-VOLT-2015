<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @license see /license.txt
 * @author autogenerated
 */
class TrackCOs extends \Entity
{
    /**
     * @return \Entity\Repository\TrackCOsRepository
     */
     public static function repository(){
        return \Entity\Repository\TrackCOsRepository::instance();
    }

    /**
     * @return \Entity\TrackCOs
     */
     public static function create(){
        return new self();
    }

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var string $os
     */
    protected $os;

    /**
     * @var integer $counter
     */
    protected $counter;


    /**
     * Get id
     *
     * @return integer 
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * Set os
     *
     * @param string $value
     * @return TrackCOs
     */
    public function set_os($value)
    {
        $this->os = $value;
        return $this;
    }

    /**
     * Get os
     *
     * @return string 
     */
    public function get_os()
    {
        return $this->os;
    }

    /**
     * Set counter
     *
     * @param integer $value
     * @return TrackCOs
     */
    public function set_counter($value)
    {
        $this->counter = $value;
        return $this;
    }

    /**
     * Get counter
     *
     * @return integer 
     */
    public function get_counter()
    {
        return $this->counter;
    }
}