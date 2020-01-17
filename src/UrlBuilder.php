<?php

namespace CodeZero\LocalizedRoutes;

class UrlBuilder
{
    /**
     * The parsed URL parts.
     *
     * @var array
     */
    protected $urlParts;

    /**
     * Crate a new UrlBuilder instance.
     *
     * @param string $url
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public static function make($url)
    {
        return new self($url);
    }

    /**
     * Crate a new UrlBuilder instance.
     *
     * @param string $url
     */
    public function __construct($url)
    {
        $this->urlParts = parse_url($url) ?: [];
    }

    /**
     * Create a string from URL parts.
     *
     * @param bool $absolute
     *
     * @return string
     */
    public function build($absolute = true)
    {
        $host = $absolute ? $this->get('scheme') . '://' . $this->get('host') . $this->get('port') : '';
        $path = '/' . trim($this->get('path'), '/');
        $query = $this->get('query') ? '?' . $this->get('query') : '';

        return  $host . $path . $query;
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->get('host');
    }

    /**
     * Set the host.
     *
     * @param string $host
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public function setHost($host)
    {
        $this->set('host', $host);

        return $this;
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->get('path');
    }

    /**
     * Set the path.
     *
     * @param string $path
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public function setPath($path)
    {
        $this->set('path', '/' . trim($path, '/'));

        return $this;
    }

    /**
     * Get the slugs.
     *
     * @return array
     */
    public function getSlugs()
    {
        return explode('/', trim($this->getPath(), '/'));
    }

    /**
     * Set the slugs.
     *
     * @param array $slugs
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public function setSlugs(array $slugs)
    {
        $this->setPath('/' . join('/', $slugs));

        return $this;
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->get('query') ? '?' . $this->get('query') : '';
    }

    /**
     * Set the query string parameters.
     *
     * @param array $query
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public function setQuery(array $query)
    {
        $this->set('query', http_build_query($query));

        return $this;
    }

    /**
     * Get the value of a URL part.
     *
     * @param string $part
     *
     * @return string
     */
    protected function get($part)
    {
        return $this->urlParts[$part] ?? '';
    }

    /**
     * Set a URL part to a new value.
     *
     * @param string $part
     * @param string $value
     *
     * @return void
     */
    protected function set($part, $value)
    {
        $this->urlParts[$part] = $value;
    }
}
