<?php
declare(strict_types=1);

namespace PatrikGrinsvall\XConsole\Traits;


use Exception;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

/**
 * Enables theming for the console command, or maybe also other classes when i think about it
 */
trait HasTheme
{
    public array $defaultThemes = [
        'default'  => [
            'text-light'          => '#e8fbb0',
            'text'                => '#D4E79E',
            'text-dark'           => '#262f17',
            'bg-light'            => '#222222',
            'bg'                  => '#0046b0',
            'bg-dark'             => '#a3fbbb',
            'complementary-light' => '#fff110',
            // used in time
            'complementary'       => '#78A1BB',
            'complementary-dark'  => '#2B0009',
            'tertiary-light'      => '#fff110',
            'tertiary'            => '#e1edff',
            'tertiary-dark'       => '#2B0009',
            // time color in date
            'error'               => '#F8333C',
            'warning'             => '#FC9E4F',
            'debug'               => '#087F8C',
            'info'                => '#4b99ee',
        ],
        'pipeline' => [
            'text-light'          => '#80b1fb',
            'text'                => '#3c55fb',
            'text-dark'           => '#000056',
            'bg-light'            => '#ffd17c',
            'bg'                  => '#ffcc00',
            'bg-dark'             => '#9c8e04',
            'complementary-light' => '#00ffec',
            'complementary'       => '#03c47b',
            'complementary-dark'  => '#265f04',
            'tertiary-light'      => '#ff81da',
            'tertiary'            => '#ff00cc',
            'tertiary-dark'       => '#be004e',
            'error'               => '#F8333C',
            'warning'             => '#fc9a00',
            'debug'               => '#0cff00',
            'info'                => '#ff01ff',
        ],
    ];
    /**
     * @var array ('text' => string,'text-light' => string,'text-dark' => string, 'bg' => string, 'bg-dark' => string, 'bg-light' => string,
     * 'complementary' => string,   'complementary-light' => string,    'complementary-dark' => string,    'tertiary' => string,    'tertiary-light' => string,    'tertiary-dark' => string,    'error' => string,    'warning' => string,    'debug' => string,    'info' => string)
     */
    protected array $themeRules = [
        'text'                => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'text-light'          => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'text-dark'           => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'bg'                  => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'bg-dark'             => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'bg-light'            => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'complementary'       => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'complementary-light' => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'complementary-dark'  => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'tertiary'            => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'tertiary-light'      => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'tertiary-dark'       => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'error'               => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'warning'             => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'debug'               => 'regex:/^#[a-fA-F0-9]{3,6}/',
        'info'                => 'regex:/^#[a-fA-F0-9]{3,6}/',
    ];
    protected array $theme      = [];
    /**
     * @var string
     */
    protected string $dateformat = 'Y-m-d', $timeformat = 'h:i:s';


    /** @var bool $validated has the theme been validated yet? */
    private bool $validated = false;

    /**
     * @var array $validators Validator[]|array[Validator]
     */
    private array $validators;

    /** @var string 'dark'|'light'|'' */
    private string $mode = 'dark';

    /**
     * Prints to standard out, this is the main output function used by theme trait
     * @param ...$args
     * @return void
     * @throws Throwable
     */
    public function stdout(...$args): string
    {
        if (!$this->validated) {
            $this->catch(function () {
                $this->validate();
            });
        }
        if (method_exists($this, 'line')) {
            $this->line($this->date() . $this->colorize($args));
        }

        return $this->date() . $this->colorize($args);
    }

    /**
     *
     * @param callable      $statement
     * @param callable|null $catch
     * @return void|null
     * @throws Exception|Throwable
     */
    public function catch(callable $statement, ?callable $catch = null)
    {
        $result = null;
        try {
            $result = $statement();
        } catch (Exception $exception) {
            try {
                $this->colorize("Exception:", $exception->getMessage());
                $catch($exception->getMessage());
            } catch (Exception $exception) {
                die('fatal error, colors or theme was not initialized when exception was thrown' . $exception->getMessage());
            }
        }

        return $result;
    }

    /**
     * @param array|string ...$strings
     * @return string
     * @throws Throwable
     */
    public function colorize($args): string
    {

        $line = '';

        if (is_string($args)) {
            $args = [ $args ];
        }

        foreach ($args as $string) {
            try {
                $line .= sprintf('<fg=%s;bg=%s> %s </>', $this->color(), $this->color('bg'), $string);

            } catch (Exception $exception) {
                /** @noinspection ForgottenDebugOutputInspection */
                dump($args, $exception, $string);
            }


        }


        return $line;
    }

    /**
     * @param string $exceptionlement
     * @param string $mode
     * @return string - returns the text using theme colors
     * @throws Throwable
     */
    public function color(string $exceptionlement = 'text', string $mode = ''): string
    {
        if (!$this->validated) $this->validate();

        if (!empty($mode)) {
            $old = $this->mode();
            $this->mode($mode);
        }
        $color = $this->theme[$this->style($exceptionlement)];
        if (!empty($old)) $this->mode($old);

        return $color;
    }

    /**
     * Validate theme settings or an input string
     * @return bool
     * @throws Throwable
     */
    public function validate(): bool
    {

        if (!isset($this->validators)) $this->registerValidator();
        if (count($this->theme) === 0) $this->setTheme($this->defaultThemes['default']);
        foreach ($this->validators as $validator) {
            /**  @var $validator Validator */
            throw_unless($validator::make(array_values($this->defaultThemes['default']), array_values($this->themeRules))->validate(), new Exception('Exception when validating theme or no theme set'));
        }
        $this->validated = true;

        return true;
    }

    /**
     * @param Validator|string|null $validator
     * @return object
     */
    public function registerValidator(Validator|string|null $validator = null): object
    {
        $this->validators[] = is_callable($validator) ? $validator : Validator::class;

        return $this;
    }

    /**
     * @param $theme
     * @return object
     * @throws Throwable
     */
    public function setTheme($theme): object
    {

        if (is_string($theme)) {
            $this->theme = $this->defaultThemes[$theme] ?? $theme;
        }
        $this->theme = $theme;


        return $this;
    }

    /**
     * @param string|null $mode
     * @return string|self
     */
    public function mode(string $mode = null): string|object
    {
        if (empty($mode)) return /** @var string */ $this->mode;
        if (in_array($mode, [
            'dark',
            'light',
            '',
        ])) {
            $this->mode = $mode;
        }

        return $this;
    }

    /**
     * @param string @Pattern('text | bg') $exceptionlement
     * @return string
     */
    public function style(string $exceptionlement = 'text'): string
    {
        return in_array($this->mode, [
            '',
            'default',
            null,
        ], true) ? $exceptionlement : "$exceptionlement-$this->mode";
    }

    /**
     * @param null $dateformat
     * @param null $timeformat
     * @return string
     * @throws Throwable
     */
    public function date($dateformat = null, $timeformat = null): string
    {

        return ' <fg=' . $this->color('complementary') . ';bg=' . $this->color('complementary', 'light') . '>[</> ' . '<fg=' . $this->color('tertiary', 'light') . ';bg=' . $this->color('tertiary', 'dark') . '>' . date($dateformat ?? $this->dateformat) . '</> <fg=' . $this->color('bg') . '>-</> ' . '<fg=' . $this->color('tertiary', 'dark') . ';bg=' . $this->color('complementary', 'light') . '>' . date($timeformat ?? $this->timeformat) . '</> <fg=' . $this->color('complementary') . ';bg=' . $this->color('complementary', 'light') . '>]</> ';
    }

    public function demoTheme()
    {
        foreach ($this->theme as $key => $value) {

            if (strpos($key, "text")) $this->line("<fg=" . $value . ">" . $key . "</>");
            $this->line('<bg=' . $value . '>' . $key . '</>');
        }


    }

    /**
     * Validate theme settings or run specified callback if it cant be validated
     * @param callable $callback
     * @return bool
     */
    public function validateOr(callable $callback): bool
    {
        foreach ($this->validators as $validator) {
            /**  @var $validator Validator */
            if ($validator::make($this->theme, array_values($this->themeRules))->passes() === false) {
                $callback("Couldn't validate theme at validator:" . class_basename($validator));
            }

        }

        return true;
    }

    /**
     * checks if environment variable for colors is set
     * @param bool $force
     * @return bool
     * @throws Throwable
     */
    public function supportsColors(bool $force = false): bool
    {
        if ($force) {
            putenv('COLORTERM=truecolor');
        }
        $env = [
            'COLORTERM'    => 'truecolor',
            'CI'           => true,
            'WT_SESSION'   => true,
            'ConEmuTask'   => [ '{cmd::Cmder}' ],
            'TERM_PROGRAM' => [ 'vscode' ],
            'TERM'         => [
                'xterm-256color',
                'alacritty',
            ],
        ];
        foreach ($env as $key => $value) {
            if (is_bool($value) && ($env[$key] === getenv($key))) {
                {
                    return true;
                }
            }
            if (is_string($value)) $value = [ $value ];
            if (is_array($value)) {
                foreach ($value as $val) {
                    if (is_bool($val) && getenv($key) && $value === getenv($key)) return true;
                    if (is_string(getenv($val)) && strtoupper(getenv($key)) == strtoupper($val)) return true;

                }
            }
        }

        return false;
    }

    /**
     * Takes many strings, paste them together and return colorized output
     *
     * @param array|string ...$strings
     * @return object
     * @throws Throwable
     */
    public function suprise(array|string ...$strings): object
    {
        if (is_string($strings)) $strings[0] = $strings;
        if (!is_array($strings)) throw new RuntimeException('Invalid argument, must be one dimensional array or a string.');
        $line = $this->date();
        foreach ($strings as $string) {
            if (is_array($string)) dD($strings);


            if (empty($string) || strpos($string, "</>")) {
                continue;
            }
            $col   = $this->randomWebColor();
            $bgcol = $this->isColorBright($col['rgb']['r'], $col['rgb']['g'], $col['rgb']['b']) ? 'bg=black' : 'bg=white';
            $line  .= sprintf('<fg=#%s;%s> %s </>', $col['hex'], $bgcol, $string);

        }
        $this->line($line . ' </></>');

        return $this;

    }

    /**
     * Returns a random hex color and a corresponding array with RGB values
     *
     * @return array( [ 'hex' => string, 'rgb' => array(['r'=>string,'g'=>string,'b'=>string])  ] )
     *
     * @throws Exception
     */
    public function randomWebColor(): array
    {
        $hex = '';
        $rgb = [];
        foreach ([
                     'r',
                     'g',
                     'b',
                 ] as $color) {
            $val    = random_int(0, 255);
            $dechex = dechex($val);
            if (strlen($dechex) < 2) {
                $dechex = '0' . $dechex;
            }
            $hex         .= strtoupper($dechex);
            $rgb[$color] = $val;
        }

        return [
            'hex' => $hex,
            'rgb' => $rgb,
        ];
    }

    /**
     * Returns true if the supplied RGB color is bright, false if dark.
     * @param int $r
     * @param int $g
     * @param int $b
     * @return bool
     */
    public function isColorBright(int $r, int $g, int $b): bool
    {
        $hsp = sqrt(0.299 * ($r * $r) + 0.587 * ($g * $g) + 0.114 * ($b * $b));

        return $hsp > 127.5;
    }

    public function rgbToHex($r, $g, $b)
    {

        foreach (func_get_args() as $col) {
            $col = dechex($col);
            if (strlen($col) < 2) {
                $dechex = '0' . $dechex;
            }
            $hex .= strtoupper($dechex);
        }

        return "#$hex";


    }

    /**
     * Returns an array with random dark RGB values
     * @return array ('rgb'=>string)
     * @throws Exception
     */
    public function randomDarkRGBColor(): array
    {
        $color = $this->randomDarkWebColor();

        return $color['rgb'];
    }

    /**
     * Returns a random colors as an array with hex and rgb
     * @return array ( [ 'hex' => string, 'rgb' => array(['r'=>string,'g'=>string,'b'=>string]) ] )
     * @throws Exception
     */
    public function randomDarkWebColor(): array
    {
        do {
            $color = $this->randomWebColor();
        } while (true === $this->isColorBright($color['rgb']['r'], $color['rgb']['g'], $color['rgb']['b']));

        return $color;
    }

    /**
     * @return array ( [ 'hex' => string, 'rgb' => 'rgb' => array(['r'=>string,'g'=>string,'b'=>string])  ] )
     * @throws Exception
     */
    public function randomLightWebColor(): array
    {
        do {
            $color = $this->randomWebColor();
        } while (false === $this->isColorBright($color['rgb']['r'], $color['rgb']['g'], $color['rgb']['b']));

        return $color;
    }

    /**
     * set light mode
     */
    public function darkMode(): self
    {
        return $this->setDarkMode();
    }

    /**
     * set dark mode
     * @return HasTheme $static
     */
    public function setDarkMode(): static
    {
        $this->mode = 'dark';

        return $this;
    }

    /**
     * set light mode
     * @return $this
     */
    public function lightMode(): static
    {
        return $this->setLightMode();
    }

    /**
     * set light mode
     * @return $this
     */
    public function setLightMode(): static
    {
        $this->mode = 'light';

        return $this;
    }

    /**
     * @param string|null $dateformat
     * @return self|string
     */
    public function dateformat(?string $dateformat = null): string|static
    {
        if ($dateformat) {
            $this->dateformat = $dateformat;

            return $this;
        }

        return $dateformat;
    }

    /**
     * @param string $attribute text/background
     * @return string
     */
    public function themecol(string $attribute = 'text'): string
    {
        return $this->theme[$this->mode($attribute)];
    }
}
