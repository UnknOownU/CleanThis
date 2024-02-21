<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* _partials/_nav.html.twig */
class __TwigTemplate_0dda8bb008efa8e77b83e49ec037aa97 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        $__internal_5a27a8ba21ca79b61932376b2fa922d2 = $this->extensions["Symfony\\Bundle\\WebProfilerBundle\\Twig\\WebProfilerExtension"];
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->enter($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "_partials/_nav.html.twig"));

        $__internal_6f47bbe9983af81f1e7450e9a3e3768f = $this->extensions["Symfony\\Bridge\\Twig\\Extension\\ProfilerExtension"];
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->enter($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof = new \Twig\Profiler\Profile($this->getTemplateName(), "template", "_partials/_nav.html.twig"));

        // line 1
        echo "<nav class=\"navbar navbar-expand-lg bg-body-tertiary\">
\t<div class=\"container-fluid\">
\t\t<a class=\"navbar-brand\" href=\"#\">CleanThis</a>
\t\t<button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
\t\t\t<span class=\"navbar-toggler-icon\"></span>
\t\t</button>
\t\t<div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">
\t\t\t<ul class=\"navbar-nav me-auto mb-2 mb-lg-0\">
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link active\" aria-current=\"page\" href=\"#\">Accueil</a>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"#\">Link</a>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item dropdown\">
\t\t\t\t\t<a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
\t\t\t\t\t\tDropdown
\t\t\t\t\t</a>
\t\t\t\t\t<ul class=\"dropdown-menu\">
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Action</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Another action</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li><hr class=\"dropdown-divider\"></li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Something else here</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t</ul>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link disabled\" aria-disabled=\"true\">Disabled</a>
\t\t\t\t</li>
\t\t\t</ul>
            <ul class=\"navbar-nav ms-auto mb-2 mb-lg-0\">
            ";
        // line 37
        if (twig_get_attribute($this->env, $this->source, (isset($context["app"]) || array_key_exists("app", $context) ? $context["app"] : (function () { throw new RuntimeError('Variable "app" does not exist.', 37, $this->source); })()), "user", [], "any", false, false, false, 37)) {
            // line 38
            echo "                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"#\">Mon compte</a>
\t\t\t\t</li>
                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"";
            // line 42
            echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_logout");
            echo "\">Deconnexion</a>
\t\t\t\t</li>
            ";
        } else {
            // line 45
            echo "                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"";
            // line 46
            echo $this->extensions['Symfony\Bridge\Twig\Extension\RoutingExtension']->getPath("app_login");
            echo "\">Me connecter</a>
\t\t\t\t</li>
            ";
        }
        // line 48
        echo "                
            </ul>
\t\t</div>
\t</div>
</nav>
";
        
        $__internal_5a27a8ba21ca79b61932376b2fa922d2->leave($__internal_5a27a8ba21ca79b61932376b2fa922d2_prof);

        
        $__internal_6f47bbe9983af81f1e7450e9a3e3768f->leave($__internal_6f47bbe9983af81f1e7450e9a3e3768f_prof);

    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "_partials/_nav.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  104 => 48,  98 => 46,  95 => 45,  89 => 42,  83 => 38,  81 => 37,  43 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("<nav class=\"navbar navbar-expand-lg bg-body-tertiary\">
\t<div class=\"container-fluid\">
\t\t<a class=\"navbar-brand\" href=\"#\">CleanThis</a>
\t\t<button class=\"navbar-toggler\" type=\"button\" data-bs-toggle=\"collapse\" data-bs-target=\"#navbarSupportedContent\" aria-controls=\"navbarSupportedContent\" aria-expanded=\"false\" aria-label=\"Toggle navigation\">
\t\t\t<span class=\"navbar-toggler-icon\"></span>
\t\t</button>
\t\t<div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">
\t\t\t<ul class=\"navbar-nav me-auto mb-2 mb-lg-0\">
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link active\" aria-current=\"page\" href=\"#\">Accueil</a>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"#\">Link</a>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item dropdown\">
\t\t\t\t\t<a class=\"nav-link dropdown-toggle\" href=\"#\" role=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
\t\t\t\t\t\tDropdown
\t\t\t\t\t</a>
\t\t\t\t\t<ul class=\"dropdown-menu\">
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Action</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Another action</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t\t<li><hr class=\"dropdown-divider\"></li>
\t\t\t\t\t\t<li>
\t\t\t\t\t\t\t<a class=\"dropdown-item\" href=\"#\">Something else here</a>
\t\t\t\t\t\t</li>
\t\t\t\t\t</ul>
\t\t\t\t</li>
\t\t\t\t<li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link disabled\" aria-disabled=\"true\">Disabled</a>
\t\t\t\t</li>
\t\t\t</ul>
            <ul class=\"navbar-nav ms-auto mb-2 mb-lg-0\">
            {% if app.user %}
                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"#\">Mon compte</a>
\t\t\t\t</li>
                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"{{ path('app_logout') }}\">Deconnexion</a>
\t\t\t\t</li>
            {% else %}
                <li class=\"nav-item\">
\t\t\t\t\t<a class=\"nav-link\" href=\"{{ path('app_login') }}\">Me connecter</a>
\t\t\t\t</li>
            {% endif %}                
            </ul>
\t\t</div>
\t</div>
</nav>
", "_partials/_nav.html.twig", "D:\\XAMPP\\htdocs\\cleanthis2\\templates\\_partials\\_nav.html.twig");
    }
}
