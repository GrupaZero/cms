<?php namespace Gzero\Cms\Presenters;

use Gzero\Core\Presenters\UserPresenter;
use Robbo\Presenter\Presenter;

class BlockPresenter extends Presenter {

    protected $author;

    protected $view;

    protected $translation;

    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'region',
        'theme',
        'weight',
        'options',
        'is_active',
        'is_cacheable'
    ];

    /**
     * ContentPresenter constructor.
     *
     * @param array $data data to create presenter instance
     */
    public function __construct(array $data)
    {
        $this->object       = array_only($data, $this->allowedAttributes);
        $this->translations = array_get($data, 'translations', []);
        $this->author       = new UserPresenter(array_get($data, 'author', []));
        $this->view         = array_get($data, 'view');

        $this->translation = array_first($this->translations, function ($translation) {
            return $translation['language_code'] === app()->getLocale();
        }, [
            'title'         => null,
            'body'          => null,
            'custom_fields' => null
        ]);
    }

    /**
     * @return mixed
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * @return mixed
     */
    public function isCacheable()
    {
        return $this->is_cacheable;
    }

    /**
     * @return mixed
     */
    public function hasTitle()
    {
        return !empty($this->getTitle());
    }

    /**
     * @return mixed
     */
    public function hasBody()
    {
        return !empty($this->getBody());
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getTitle(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'title');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'title');
    }


    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getBody(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'body');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'body');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function getCustomFields(string $language = null): ?string
    {
        if ($language === null) {
            return array_get($this->translation, 'custom_fields');
        }

        $translation = array_first($this->translations, function ($translation) use ($language) {
            return $translation['language_code'] === $language;
        });

        return array_get($translation, 'custom_fields');
    }

    /**
     * @param null $default default value if there is no theme
     *
     * @return string
     */
    public function getTheme($default = null)
    {
        return array_get($this, 'theme', $default);
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return array_get($this, 'region');
    }

    /**
     * @return string
     */
    public function getWeight()
    {
        return array_get($this, 'weight');
    }

    /**
     * @return string
     */
    public function getOptions()
    {
        return array_get($this, 'options');
    }

    /**
     * This function returns author first and last name
     *
     * @return UserPresenter
     */
    public function getAuthor()
    {
        return optional($this->author);
    }

}
