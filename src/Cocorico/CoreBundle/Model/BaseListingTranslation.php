<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Model;

use Cocorico\CoreBundle\Entity\Listing;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 *
 */
abstract class BaseListingTranslation
{

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\NotNull(message="assert.not_blank")
     * @Assert\Length(
     *      min = "3",
     *      max = "50",
     *      minMessage = "assert.min_length {{ limit }}",
     *      maxMessage = "assert.max_length {{ limit }}"
     * )
     *
     * @ORM\Column(name="title", type="string", length=50, nullable=false)
     *
     * @var string
     */
    protected $title;

    /**
     * @Assert\NotBlank(message="assert.not_blank")
     * @Assert\NotNull(message="assert.not_blank")
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(name="rules", type="text", length=65535, nullable=true)
     *
     * @var string
     */
    protected $rules;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getTranslatableId()
    {
        return $this->translatable->getId();
    }

    public function getSluggableFields()
    {
        return ['title', 'translatableId'];
    }

    /**
     * Set title
     *
     * @param  string $title
     * @return Listing
     */
    public function setTitle($title)
    {
        $this->title = ucfirst($title);

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param  string $description
     * @return Listing
     */
    public function setDescription($description)
    {
        $this->description = $description;

//        if (in_array("phone", $typeFilter)) {
//            $pattern = "(0[0-9])?([-. ]?[0-9]{2}){4}";
//            //$pattern = "(([-. _|,;\!\$\?\!\#:|@]?){1,}[0-9]{2}){4,}";
//            //$pattern = "(([-. _|,;\/\!\$\?\!\#:|@]?){1,}[0-9]{2}){4,}";
//            //$pattern = "^0[1-68]([-. ]?[0-9]{2}){4}$";
//            //$sep = "((-|\.| |_|,|;|/){0,}[0-9]{2}){4,}";
//            $content = preg_replace("#$pattern#", $replaceBy, $content);
//
//            $pattern = "\+[0-9]{1}([-. ]?[0-9]){10}";
//            $content = preg_replace("#$pattern#", $replaceBy, $content);
//        }
//        if (in_array("email", $typeFilter)) {
//            $pattern = "[a-zA-Z0-9_.+-]+(@)[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+";
//            $content = preg_replace("#$pattern#", $replaceBy, $content);
//        }
//        if (in_array("address", $typeFilter)) {
//            //$pattern = "([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}";
//            $pattern = "([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+(com|fr|co|org|net|biz|tv|info)";
//            $content = preg_replace("#$pattern#", $replaceBy, $content);
//        }

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     *
     * @param  string $rules
     * @return Listing
     */
    public function setRules($rules)
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Get rules
     *
     * @return string
     */
    public function getRules()
    {
        return $this->rules;
    }

}
