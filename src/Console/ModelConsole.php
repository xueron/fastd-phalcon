<?php
/**
 * ModelConsole.php
 *
 */

namespace Xueron\FastDPhalcon\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ModelConsole extends Command
{
    public function configure()
    {
        $this->setName('model:create');
        $this->setDescription('Create a model');
        $this->addArgument('name', InputArgument::REQUIRED, 'Model name');
        $this->addOption('table', null, InputOption::VALUE_OPTIONAL, 'Table name, Default: same with model name', null);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $modelPath = app()->getPath() . '/src/Model';
        if (!file_exists($modelPath)) {
            mkdir($modelPath, 0755, true);
        }

        $name = ucfirst($input->getArgument('name'));
        $table = $input->getOption('table');

        $modelFile = $modelPath . '/' . $name . '.php';
        if (file_exists($modelFile)) {
            throw new \LogicException(sprintf('Model %s is already exists', $name));
        }

        $content = $this->createModelTemplate($name, $table);
        file_put_contents($modelFile, $content);
        $output->writeln(sprintf('Model %s created successful. Path in %s', $name, $modelPath));
    }

    public function createModelTemplate($name, $table)
    {
        $table = $this->tableTemplate($table);
        return <<<MODEL
<?php
namespace Model;

use Xueron\FastDPhalcon\Model\Model;

class {$name} extends Model
{
{$table}
}
MODEL;
    }

    public function tableTemplate($table = null)
    {
        if ($table) {
            return <<<TABLE
    public function getSource()
    {
        return '{$table}';
    }
TABLE;
        }
        return '';
    }
}