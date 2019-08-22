<?php
declare(strict_types=1);

namespace Cundd\Rest\Bootstrap\Language;

use Cundd\Rest\Bootstrap\Language\Exception\MissingLanguageCodeException;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class LanguageInformation
{
    /**
     * @var int
     */
    private $uid;

    /**
     * @var string
     */
    private $code;

    /**
     * LanguageInformation constructor.
     *
     * @param int    $uid
     * @param string $code
     */
    public function __construct(int $uid, ?string $code)
    {
        $this->uid = $uid;
        if (!$code) {
            throw new MissingLanguageCodeException('Two Letter ISO Code must be given');
        }
        $this->code = $code;
    }

    public static function fromSiteLanguage(SiteLanguage $siteLanguage): self
    {
        return new static($siteLanguage->getLanguageId(), $siteLanguage->getTwoLetterIsoCode());
    }

    /**
     * @return int
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
