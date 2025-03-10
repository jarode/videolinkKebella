<form method="POST" action="/login">
  @csrf
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="nip" placeholder="NIP" required>
  <button>Zaloguj</button>
</form>

@if($errors->any())
  <div>{{ $errors->first() }}</div>
@endif
