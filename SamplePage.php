<?php include "../inc/dbinfo.inc"; ?>
<html>
<body>
<h1>Sample page</h1>
<?php

  /* Connect to MySQL and select the database. */
  $connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

  if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

  $database = mysqli_select_db($connection, DB_DATABASE);

  /* Ensure that the ALUNOS table exists. */
  VerifyAlunosTable($connection, DB_DATABASE);

  /* If input fields are populated, add a row to the ALUNOS table. */
  $aluno_nome = htmlentities($_POST['NOME']);
  $aluno_idade = intval($_POST['IDADE']);
  $aluno_sexo = htmlentities($_POST['SEXO']);
  $aluno_aprovado = isset($_POST['APROVADO']) ? 1 : 0; // Converte para 1 se marcado, 0 caso contrário

  if (strlen($aluno_nome) || strlen($aluno_sexo) || $aluno_idade || isset($_POST['APROVADO'])) {
    AddAluno($connection, $aluno_nome, $aluno_idade, $aluno_sexo, $aluno_aprovado);
  }

  /* If a delete request is made, remove the row from the ALUNOS table. */
  if (isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    DeleteAluno($connection, $delete_id);
  }

  /* Reset AUTO_INCREMENT to the next available ID */
  ResetAutoIncrement($connection);
?>

<!-- Input form -->
<form action="<?PHP echo $_SERVER['PHP_SELF']; ?>" method="POST">
  <table border="0">
    <tr>
      <td>NOME</td>
      <td>IDADE</td>
      <td>SEXO</td>
      <td>APROVADO</td>
    </tr>
    <tr>
      <td>
        <input type="text" name="NOME" maxlength="45" size="30" />
      </td>
      <td>
        <input type="number" name="IDADE" min="0" max="150" />
      </td>
      <td>
        <select name="SEXO">
          <option value="Masculino">Masculino</option>
          <option value="Feminino">Feminino</option>
          <option value="Outro">Outro</option>
        </select>
      </td>
      <td>
        <input type="checkbox" name="APROVADO" />
      </td>
      <td>
        <input type="submit" value="Add Data" />
      </td>
    </tr>
  </table>
</form>

<!-- Display table data. -->
<table border="1" cellpadding="2" cellspacing="2">
  <tr>
    <td>ID</td>
    <td>NOME</td>
    <td>IDADE</td>
    <td>SEXO</td>
    <td>APROVADO</td>
    <td>DELETE</td>
  </tr>

<?php

$result = mysqli_query($connection, "SELECT * FROM ALUNOS");

while($query_data = mysqli_fetch_row($result)) {
  echo "<tr>";
  echo "<td>", $query_data[0], "</td>",
       "<td>", $query_data[1], "</td>",
       "<td>", $query_data[2], "</td>",
       "<td>", $query_data[3], "</td>",
       "<td>", ($query_data[4] ? 'Sim' : 'Não'), "</td>";
  echo "<td>
          <form action='", $_SERVER['PHP_SELF'], "' method='POST'>
            <input type='hidden' name='delete_id' value='", $query_data[0], "' />
            <input type='submit' value='Delete' />
          </form>
        </td>";
  echo "</tr>";
}
?>

</table>

<!-- Clean up. -->
<?php

  mysqli_free_result($result);
  mysqli_close($connection);

?>

</body>
</html>


<?php

/* Add an aluno to the table. */
function AddAluno($connection, $nome, $idade, $sexo, $aprovado) {
   $n = mysqli_real_escape_string($connection, $nome);
   $i = intval($idade);
   $s = mysqli_real_escape_string($connection, $sexo);
   $a = intval($aprovado);

   $query = "INSERT INTO ALUNOS (NOME, IDADE, SEXO, APROVADO) VALUES ('$n', $i, '$s', $a);";

   if(!mysqli_query($connection, $query)) echo("<p>Error adding aluno data.</p>");
}

/* Delete an aluno from the table. */
function DeleteAluno($connection, $id) {
   $id = intval($id);
   $query = "DELETE FROM ALUNOS WHERE ID = $id";

   if(!mysqli_query($connection, $query)) echo("<p>Error deleting aluno data.</p>");
}

/* Reset AUTO_INCREMENT to the next available ID */
function ResetAutoIncrement($connection) {
  $result = mysqli_query($connection, "SELECT MAX(ID) FROM ALUNOS");
  $row = mysqli_fetch_array($result);
  $maxId = $row[0];

  // Se a tabela estiver vazia, o próximo ID deve ser 1
  if ($maxId === null) {
    $nextId = 1;
  } else {
    $nextId = $maxId + 1;
  }

  $query = "ALTER TABLE ALUNOS AUTO_INCREMENT = $nextId";

  if(!mysqli_query($connection, $query)) echo("<p>Error resetting AUTO_INCREMENT.</p>");
}

/* Check whether the table exists and, if not, create it. */
function VerifyAlunosTable($connection, $dbName) {
  if(!TableExists("ALUNOS", $connection, $dbName))
  {
     $query = "CREATE TABLE ALUNOS (
         ID int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         NOME VARCHAR(45),
         IDADE INT,
         SEXO VARCHAR(10),
         APROVADO BOOLEAN
       )";

     if(!mysqli_query($connection, $query)) echo("<p>Error creating table.</p>");
  }
}

/* Check for the existence of a table. */
function TableExists($tableName, $connection, $dbName) {
  $t = mysqli_real_escape_string($connection, $tableName);
  $d = mysqli_real_escape_string($connection, $dbName);

  $checktable = mysqli_query($connection,
      "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_NAME = '$t' AND TABLE_SCHEMA = '$d'");

  if(mysqli_num_rows($checktable) > 0) return true;

  return false;
}
?>
