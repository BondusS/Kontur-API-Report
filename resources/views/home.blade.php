<!DOCTYPE html>
<html>
	<h1>Форма отправки запроса</h1>
	<form method="post" action="check">
		@csrf
		<h3>Введите ключ API : </h3>
		<input type="sring"
		       name="key"
		       id="key"><br>
		<h3>Введите ИНН : </h3>
		<input type="sring"
		       name="inn"
		       id="inn"><br>
		<button type="submit">Запросить</button>
	</form>
</html>