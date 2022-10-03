<?php
// modules/abancarts/src/Entity/AbandcartEmail.php
namespace Yourintellidata\Module\Abandcarts\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Table()
 * @ORM\Entity()
 */
class AbandcartEmail
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id_cart", type="integer")
     */
    private $cartId;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="date_sent", type="datetime")
     */
    private $dateSent;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", options={"default" : 1})
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="id_customer", type="integer")
     */
    private $customerId;

    /**
     * @var int
     *
     * @ORM\Column(name="id_cart_rule", type="integer")
     */
    private $cartruleId;

    /**
     * @return int $cartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @param int $cartId
     *
     * @return AbandcartEmail
     */
    public function setCartId($cartId)
    {
        $this->cartId = $cartId;

        return $this;
    }

    /**
     * Set dateSent.
     *
     * @param DateTime $dateSent
     *
     * @return AbandcartEmail
     */
    public function setDateSent(DateTime $dateSent)
    {
        $this->dateSent = $dateSent;

        return $this;
    }

    /**
     * Get dateSent.
     *
     * @return DateTime
     */
    public function getDateSent()
    {
        return $this->dateSent;
    }

    /**
     * @return int $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     *
     * @return AbandcartEmail
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

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
     * @return AbandcartEmail
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return int $cartruleId
     */
    public function getCartRuleId()
    {
        return $this->cartruleId;
    }

    /**
     * @param int $cartruleId
     *
     * @return AbandcartEmail
     */
    public function setCartRuleId($cartruleId)
    {
        $this->cartruleId = $cartruleId;

        return $this;
    }
}
