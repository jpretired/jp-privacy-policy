<?php

/**
 * Custom footer and Privacy policy for Czech locale environment.
 * Partly inspired by mp, see:
 * https://www.webtrees.net/index.php/en/forum/help-for-2-0/35233-how-to-edit-the-privacy-policy-and-the-footer#82090
 * Later adopted the MikeT's way of contact the administrator, see:
 * https://www.webtrees.net/index.php/en/forum/help-for-2-0/35233-how-to-edit-the-privacy-policy-and-the-footer#84085
 * Author: Josef Prause
 */

declare(strict_types=1);

namespace JpNamespace;

use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\PrivacyPolicy;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Registry;

use function assert;
use function view;

return new class extends PrivacyPolicy implements ModuleCustomInterface {
    use ModuleCustomTrait;

    /** @var ModuleService */
    private $module_service;

    /** @var UserService */
    private $user_service;

    private $language_switch;
  
    public function __construct() {
        parent::__construct(
            $this->module_service = new ModuleService(),
            $this->user_service = new UserService()
        );
    }
    
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
        return '1.0.6';
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
        $this->language_switch = $language;

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

        if ($tree === null) {
            return '';
        }

        $url = route('module', [
            'module' => $this->name(),
            'action' => 'Page',
            'tree'   => $tree ? $tree->name() : null,
        ]);
        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);

        return view($this->name() . '::footer', [
            'url' => $url,
            'uses_analytics' => $this->analyticsModules($tree, $user)->isNotEmpty(),
        ]);
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
        switch ($this->language_switch) {
            case 'cs':
            case 'sk':
                $page = '::page-cs';
                break;
            default:
                $page = '::page';
        }
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);
        
        $administrators = $this->user_service->administrators();
        $contactlinks = array();
        foreach ($administrators as $administrator) {
        	$user_id = $administrator->id();
        	$contactlinks[$user_id] = $this->contactLink($administrator);
        }

        return $this->viewResponse($this->name() . $page, [
            'administrators' => $administrators,
            'analytics'      => $this->analyticsModules($tree, $user),
            'title' => $this->title(),
            'tree'  => $request->getAttribute('tree'),
            'contactlinks' => $contactlinks,
        ]);
    }
    
    /**
     * Create a contact link for a user.
     *
     * @param User $user
     *
     * @return string
     */
    public function contactLink(User $user): string
    {
        if ($user instanceof User) {
// app ve wt 2.2 neexistuje, místo toho je Registry::container()->get
// viz https://github.com/fisharebest/webtrees/issues/5080
//          $request = app(ServerRequestInterface::class); ... webtrees 2.1
            $request = Registry::container()->get(ServerRequestInterface::class); // webtrees 2.2
            return $this->user_service->contactLink($user, $request);
          }
    }
};
