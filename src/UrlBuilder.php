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
    public static function make(string $url): UrlBuilder
    {
        return new self($url);
    }

    /**
     * Crate a new UrlBuilder instance.
     *
     * @param string $url
     */
    public function __construct(string $url)
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
    public function build(bool $absolute = true): string
    {
        $url = '';

        if ($absolute === true) {
            $url .= $this->getScheme() . $this->getHost() . $this->getPort();
        }

        if ($this->getPath() !== '/') {
            $url .= $this->getPath();
        }

        $url .= $this->getQueryString();

        return $url;
    }

    /**
     * Get the scheme.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->get('scheme') . '://';
    }

    /**
     * Get the host.
     *
     * @return string
     */
    public function getHost(): string
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
    public function setHost(string $host): UrlBuilder
    {
        $this->set('host', $host);

        return $this;
    }

    /**
     * Get the port.
     *
     * @return string
     */
    public function getPort(): string
    {
        $port = $this->get('port');

        return $port ? ":{$port}" : '';
    }

    /**
     * Get the path.
     *
     * @return string
     */
    public function getPath(): string
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
    public function setPath(string $path): UrlBuilder
    {
        $this->set('path', '/' . trim($path, '/'));

        return $this;
    }

    /**
     * Get the slugs.
     *
     * @return array
     */
    public function getSlugs(): array
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
    public function setSlugs(array $slugs): UrlBuilder
    {
        $this->setPath('/' . join('/', $slugs));

        return $this;
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public function getQueryString(): string
    {
        return $this->get('query') ? '?' . $this->get('query') : '';
    }

    /**
     * Get the query string as an array.
     *
     * @return array
     */
    public function getQueryStringArray(): array
    {
        $query = $this->get('query');
        $queryArray = [];

        if ( ! $query) {
            return $queryArray;
        }

        $pairs = explode('&', $query);

        foreach ($pairs as $pair) {
            $pair = explode('=', $pair);
            $key = $pair[0] ?? null;
            $value = $pair[1] ?? null;
            $queryArray[$key] = $value;
        }

        return  $queryArray;
    }

    /**
     * Set the query string parameters.
     *
     * @param array $query
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    public function setQueryString(array $query): UrlBuilder
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
    protected function get(string $part): string
    {
        return $this->urlParts[$part] ?? '';
    }

    /**
     * Set a URL part to a new value.
     *
     * @param string $part
     * @param string $value
     *
     * @return \CodeZero\LocalizedRoutes\UrlBuilder
     */
    protected function set(string $part, string $value): UrlBuilder
    {
        $this->urlParts[$part] = $value;

        return $this;
    }
}
