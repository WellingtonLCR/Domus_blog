$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8001/api'

Write-Host '0) Health check'
Invoke-RestMethod -Method Get -Uri 'http://127.0.0.1:8001/up' | Out-Null
Write-Host 'OK: /up respondeu'

Write-Host '1) Login ADMIN'
$adminLogin = Invoke-RestMethod -Method Post -Uri "$base/auth/admin/login" -ContentType 'application/json' -Body '{"usuario":"admin","senha":"admin123"}'
$adminToken = $adminLogin.data.token
Write-Host ("Admin token obtido? {0}" -f [bool]$adminToken)
if (-not $adminToken) { throw 'Falha ao obter token do admin. Resposta inesperada.' }

Write-Host '2) Login USUARIO (joao)'
$userLogin = Invoke-RestMethod -Method Post -Uri "$base/auth/usuario/login" -ContentType 'application/json' -Body '{"usuario":"joao","senha":"senha123"}'
$userToken = $userLogin.data.token
Write-Host ("User token obtido? {0}" -f [bool]$userToken)
if (-not $userToken) { throw 'Falha ao obter token do usuario. Resposta inesperada.' }

$joaoId = $userLogin.data.usuario.id
if (-not $joaoId) { throw 'Falha ao obter joaoId via login do usuario.' }
Write-Host "joaoId = $joaoId"

Write-Host '3) (skip) Buscar joaoId por listagem de usuarios'

Write-Host '4) Criar post como joao (rascunho)'
$titulo = 'Post Teste ' + (Get-Date -Format 'yyyyMMddHHmmss')
$postBody = @{ titulo = $titulo; status = 'rascunho'; texto = 'Conteudo de teste simples' } | ConvertTo-Json -Depth 3
$postResp = Invoke-RestMethod -Method Post -Uri "$base/posts" -Headers @{ Authorization = "Bearer $userToken" } -ContentType 'application/json' -Body $postBody
$postId = $postResp.data.id
Write-Host "postId = $postId"

Write-Host '5) Publicar post como joao'
$published = Invoke-RestMethod -Method Post -Uri "$base/posts/$postId/publish" -Headers @{ Authorization = "Bearer $userToken" } -ContentType 'application/json'
Write-Host ("status apos publish = {0}" -f $published.data.status)

Write-Host '6) Ver post como VISITANTE (sem token)'
$visitorView = Invoke-RestMethod -Method Get -Uri "$base/posts/$postId"
Write-Host ("visitante conseguiu ver? {0}" -f [bool]$visitorView.data.id)

Write-Host '7) Banir usuario joao como ADMIN'
$ban = Invoke-RestMethod -Method Post -Uri "$base/usuarios/$joaoId/ban" -Headers @{ Authorization = "Bearer $adminToken" } -ContentType 'application/json'
Write-Host ("banido_em = {0}" -f $ban.data.banido_em)

Write-Host '8) Confirmar bloqueio: tentar criar post como joao (token antigo)'
try {
  $post2Body = @{ titulo = ('Post Bloqueado ' + (Get-Date -Format 'yyyyMMddHHmmss')); status = 'rascunho'; texto = 'Nao deveria criar' } | ConvertTo-Json -Compress
  $null = Invoke-RestMethod -Method Post -Uri "$base/posts" -Headers @{ Authorization = "Bearer $userToken" } -ContentType 'application/json' -Body $post2Body
  Write-Host 'ERRO: conseguiu criar post mesmo banido.'
} catch {
  Write-Host ('OK: bloqueado ao criar post. ' + $_.Exception.Message)
}

Write-Host '9) Confirmar bloqueio: tentar login novamente como joao'
try {
  $null = Invoke-RestMethod -Method Post -Uri "$base/auth/usuario/login" -ContentType 'application/json' -Body '{"usuario":"joao","senha":"senha123"}'
  Write-Host 'ERRO: conseguiu logar mesmo banido.'
} catch {
  Write-Host ('OK: login bloqueado. ' + $_.Exception.Message)
}

Write-Host 'FIM'
