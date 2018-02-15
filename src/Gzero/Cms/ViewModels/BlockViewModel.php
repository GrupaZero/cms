<?php namespace Gzero\Cms\ViewModels;

use Gzero\Core\ViewModels\UserViewModel;

class BlockViewModel {

    /** @var array */
    protected $data;

    /** @var array */
    protected $author;

    /** @var array */
    protected $translation;

    /** @var array */
    protected $translations;

    /** @var array */
    protected $allowedAttributes = [
        'id',
        'view',
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
        $this->data         = array_only($data, $this->allowedAttributes);
        $this->translations = array_get($data, 'translations', []);
        $this->author       = new UserViewModel(array_get($data, 'author', []));

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
        return array_get($this->data, 'is_active', false);
    }

    /**
     * @return mixed
     */
    public function isCacheable()
    {
        return array_get($this->data, 'is_cacheable', false);
    }

    /**
     * @return mixed
     */
    public function hasTitle()
    {
        return !empty($this->title());
    }

    /**
     * @return mixed
     */
    public function hasBody()
    {
        return !empty($this->body());
    }

    /**
     * @return integer
     */
    public function id()
    {
        return array_get($this->data, 'id');
    }

    /**
     * @return string
     */
    public function view()
    {
        return array_get($this->data, 'view');
    }

    /**
     * @param string|null $language optional language code to search for
     *
     * @return string
     */
    public function title(string $language = null): ?string
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
    public function body(string $language = null): ?string
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
    public function customFields(string $language = null): ?string
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
    public function theme($default = null)
    {
        return array_get($this->data, 'theme') ?: $default;
    }

    /**
     * @return string
     */
    public function region()
    {
        return array_get($this->data, 'region');
    }

    /**
     * @return string
     */
    public function weight()
    {
        return array_get($this->data, 'weight');
    }

    /**
     * @return string
     */
    public function options()
    {
        return array_get($this->data, 'options');
    }

    /**
     * This function returns author first and last name
     *
     * @return UserViewModel
     */
    public function author()
    {
        return optional($this->author);
    }

}
