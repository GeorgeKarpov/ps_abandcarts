<?php
// modules/abancarts/src/Entity/AbandcartCustomer.php
namespace Yourintellidata\Module\Abandcarts\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class AbandcartCustomer
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_customer", type="integer")
     */
    private $customerId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_added", type="datetime")
     */
    private $dateAdded;

 
    /**
     * @return int $customerId
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param int $customerId
     *
     * @return AbandcartCustomer
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

   /**
     * Set dateAdded.
     *
     * @param DateTime $dateAdded
     *
     * @return AbandcartCustomer
     */
    public function setDateAdded(DateTime $dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded.
     *
     * @return DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

}
