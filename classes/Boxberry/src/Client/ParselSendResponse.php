<?php
/**
 *
 *  * This file is part of Boxberry Api.
 *  *
 *  * For the full copyright and license information, please view LICENSE
 *  * file that was distributed with this source code
 *  *
 *  * File: ParselSendResponse.php
 *  * Created: 05.02.2021
 *  *
 */

namespace Boxberry\Client;

use Boxberry\Client\Exceptions\BadResponseException;

/**
 * Class ParselSendResponse
 * @package Boxberry\Client
 */
class ParselSendResponse
{
    /**
     * @var string
     */
    protected $id = null;
    /**
     * @var string
     */
    protected $label = null;
    /**
     * @var string
     */
    protected $url_tmc = null;
    /**
     * @var string
     */
    protected $sticker = null;


    /**
     * ParselSendResponse constructor.
     * @param array $data
     * @throws BadResponseException
     */
    public function __construct(array $data)
    {
        if (isset($data['label'])) {
            $this->label = $data['label'];
        } else {
            throw new BadResponseException;
        }
        if (isset($data['id'])) {
            $this->id = $data['id'];
        } else {
            throw new BadResponseException;
        }
      /*  if (isset($data['url_tmc'])) {
            $this->url_tmc = $data['url_tmc'];
        } else {
            throw new BadResponseException;
        } */
        if (isset($data['sticker'])) {
            $this->sticker = $data['sticker'];
        } else {
            throw new BadResponseException;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getActLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setActLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getUrl_Tmc()
    {
        return $this->url_tmc;
    }

    /**
     * @param string $url_tmc
     */
    public function setUrl_tms($url_tmc)
    {
        $this->url_tmc = $url_tmc;
    }

    /**
     * @return string
     */
    public function getSticker()
    {
        return $this->sticker;
    }

    /**
     * @param string $sticker
     */
    public function setSticker($sticker)
    {
        $this->sticker = $sticker;
    }

}