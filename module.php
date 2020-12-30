<?php

/**
 * Example footer with a link to a page of information.
 */

declare(strict_types=1);

namespace JpNamespace;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleFooterInterface;
use Fisharebest\Webtrees\Module\ModuleFooterTrait;
use Fisharebest\Webtrees\View;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

return new class extends AbstractModule implements ModuleCustomInterface, ModuleFooterInterface {
    use ModuleCustomTrait;
    use ModuleFooterTrait;

    private $lang_switch;
    
    /**
     * @return string
     */
    public function title(): string
    {
        return I18N::translate('Privacy policy');
    }
    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        /* I18N: Description of the “Simple Menu” module */
        return I18N::translate('Show a privacy policy');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName(): string
    {
        return 'Josef Prause';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     *
     * We use a system where the version number is equal to the latest version of webtrees
     * Interim versions get an extra sub number
     *
     * The dev version is always one step above the latest stable version of this module
     * The subsequent stable version depends on the version number of the latest stable version of webtrees
     *
     */
    public function customModuleVersion(): string
    {
        return '1.0.1';
    }

    /**
     * A URL that will provide the latest stable version of this module.
     *
     * @return string
     */
    public function customModuleLatestVersionUrl(): string
    {
        return 'https://github.com/jpretired/jp-privacy-policy/releases/latest';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     */
    public function customModuleSupportUrl(): string
    {
        return 'https://github.com/jpretired/jp-privacy-policy/issues';
    }

    /**
     * Bootstrap the module
     */
    public function boot(): void
    {
        // Register a namespace for our views.
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
    }

    /**
     * Where does this module store its resources
     *
     * @return string
     */
    public function resourcesFolder(): string
    {
        return __DIR__ . '/resources/';
    }

    /**
     * Additional/updated translations.
     *
     * @param string $language
     *
     * @return array<string,string>
     */
    public function customTranslations(string $language): array
    {
        $this->lang_switch = $language;

        return [];
    }
    /**
     * A footer, to be added at the bottom of every page.
     *
     * @param ServerRequestInterface $request
     *
     * @return string
     */
    public function getFooter(ServerRequestInterface $request): string
    {
        $tree = $request->getAttribute('tree');

        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Page',
            'tree'   => $tree ? $tree->name() : null,
        ]);

        return view($this->name() . '::footer', ['url' => $url]);
    }

    /**
     * Generate the page that will be shown when we click the link in the footer.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getPageAction(ServerRequestInterface $request): ResponseInterface
    {
        $page = '';
        switch ($this->lang_switch) {
            case 'cs':
                $page = '::page-cs';
                break;
            default:
                $page = '::page';
        }
        return $this->viewResponse($this->name() . $page, [
            'title' => $this->title(),
            'tree'  => $request->getAttribute('tree'),
        ]);
    }
};
