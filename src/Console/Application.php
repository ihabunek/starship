<?php

namespace Starship\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

class Application extends BaseApplication
{
    const VERSION = '@starship_version@';
    const RELEASE_DATE = '@starship_release_date@';

    private static $logo = '
   _____ __                  __    _
  / ___// /_____ ___________/ /_  (_)___
  \__ \/ __/ __ `/ ___/ ___/ __ \/ / __ \
 ___/ / /_/ /_/ / /  (__  ) / / / / /_/ /
/____/\__/\__,_/_/  /____/_/ /_/_/ .___/
                                /_/
  Static site generator for PHP';

	/** Set project name and version. */
    public function __construct()
    {
        parent::__construct('Starship', self::VERSION);
    }

    /** Add logo to help text. */
    public function getHelp()
    {
        return trim(self::$logo, "\r\n") . "\n\n" . parent::getHelp();
    }

    /** Add version & release date to help text. */
    public function getLongVersion()
    {
        return parent::getLongVersion() .
            sprintf(' released on <comment>%s</comment>', self::RELEASE_DATE);
    }

	 /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new BuildCommand();
        $defaultCommands[] = new InitCommand();
        $defaultCommands[] = new ServeCommand();
        return $defaultCommands;
    }
}
