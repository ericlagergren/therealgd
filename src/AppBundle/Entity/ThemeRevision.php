<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Entity()
 * @ORM\Table(name="theme_revisions")
 */
class ThemeRevision {
    /**
     * @ORM\Column(type="uuid")
     * @ORM\Id()
     *
     * @var Uuid
     */
    private $id;

    /**
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="Theme", inversedBy="revisions")
     *
     * @var Theme
     */
    private $theme;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $commonCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $dayCss;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    private $nightCss;

    /**
     * @ORM\Column(type="boolean", options={"default": true})
     *
     * @var bool
     */
    private $appendToDefaultStyle;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $comment;

    /**
     * @ORM\Column(type="datetimetz")
     *
     * @var \DateTime
     */
    private $modified;

    /**
     * @ORM\ManyToOne(targetEntity="ThemeRevision")
     *
     * @var ThemeRevision
     */
    private $parent;

    public function __construct(
        Theme $theme,
        $commonCss,
        $dayCss,
        $nightCss,
        bool $appendToDefaultStyle,
        $comment,
        ThemeRevision $parent = null,
        \DateTime $modified = null
    ) {
        if (!$commonCss && !$dayCss && !$nightCss) {
            throw new \DomainException('At least one CSS field must be filled');
        }

        if ($parent->parent->parent->parent ?? false) {
            throw new \DomainException('A theme cannot have more than three parents');
        }

        $this->id = Uuid::uuid4();
        $this->theme = $theme;
        $this->commonCss = $commonCss;
        $this->dayCss = $dayCss;
        $this->nightCss = $nightCss;
        $this->appendToDefaultStyle = $appendToDefaultStyle;
        $this->comment = $comment;
        $this->parent = $parent;
        $this->modified = $modified ?: new \DateTime('@'.time());
        $theme->addRevision($this);
    }

    public function getId(): Uuid {
        return $this->id;
    }

    public function getTheme(): Theme {
        return $this->theme;
    }

    /**
     * @return null|string
     */
    public function getCommonCss() {
        return $this->commonCss;
    }

    /**
     * @return null|string
     */
    public function getDayCss() {
        return $this->dayCss;
    }

    /**
     * @return null|string
     */
    public function getNightCss() {
        return $this->nightCss;
    }

    public function appendToDefaultStyle(): bool {
        return $this->appendToDefaultStyle;
    }

    /**
     * @return string|null
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @return ThemeRevision|null
     */
    public function getParent() {
        return $this->parent;
    }

    public function getParentCount(): int {
        $count = 0;

        while (($parent = ($parent ?? $this)->getParent())) {
            $count++;
        }

        return $count;
    }

    /**
     * Get all parents and self in the correct include order.
     *
     * @return string[]
     */
    public function getHierarchy(): array {
        $hierarchy = [];

        while (($parent = ($parent ?? $this)->getParent())) {
            array_unshift($hierarchy, $parent);
        }

        $hierarchy[] = $this;

        return $hierarchy;
    }

    public function getModified(): \DateTime {
        return $this->modified;
    }
}
