

use yii\db\Migration;

class {{name}} extends Migration
{
    private $tables = {{data}};
    public function up()
    {
        if ($this->db->schema instanceof yii\db\mysql\Schema) {
            $this->db->createCommand("SET FOREIGN_KEY_CHECKS = 0");
        }
        foreach ($this->tables as $table => $data) {
            $this->db->createCommand()->truncateTable($table);
            if (!$data) {
                continue;
            }
            $columns = array_keys(reset($data));
            $data = $this->prepareData($data);
            $this->db->createCommand()->batchInsert($table, $columns, $data)->execute();
        }
        if ($this->db->schema instanceof yii\db\mysql\Schema) {
            $this->db->createCommand("SET FOREIGN_KEY_CHECKS = 1");
        }
        return true;
    }

    public function down()
    {
        return true;
    }

    private function prepareData($data)
    {
        array_walk($data, function(&$item) {
            $item = array_values($item);
        });
        return $data;
    }
}