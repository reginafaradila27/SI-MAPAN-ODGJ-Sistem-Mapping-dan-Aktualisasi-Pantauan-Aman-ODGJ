<?php
declare(strict_types=1);
session_start();
const DB_HOST = '127.0.0.1';
const DB_NAME = 'simapan_odgj';
const DB_USER = 'root';
const DB_PASS = '';
try { $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]); }
catch(PDOException $e){ http_response_code(500); exit('Koneksi database gagal. Periksa config/db.php dan impor sql/simapan.sql.'); }
function e($s): string { return htmlspecialchars((string)($s??''),ENT_QUOTES,'UTF-8'); }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals(csrf(),(string)($_POST['csrf']??''))) { http_response_code(403); exit('Token keamanan tidak valid.'); } }
function auth(): void { if(empty($_SESSION['uid'])){header('Location: login.php');exit;} }
function current_user(): array {return ['id'=>(int)($_SESSION['uid']??0),'nama'=>$_SESSION['nama']??'','role'=>$_SESSION['role']??'','puskesmas_id'=>$_SESSION['puskesmas_id']??null];}
function admin(): void {if(($_SESSION['role']??'')!=='admin'){http_response_code(403);exit('Akses hanya untuk administrator.');}}
function patient_scope(string $alias='p'): array { $u=current_user(); if($u['role']==='puskesmas') return ["$alias.puskesmas_id = ?",[(int)$u['puskesmas_id']]]; return ['1=1',[]]; }
function patient_access(PDO $pdo,int $id): bool { [$w,$a]=patient_scope();$s=$pdo->prepare("SELECT 1 FROM patients p WHERE p.id=? AND $w");$s->execute(array_merge([$id],$a));return (bool)$s->fetchColumn(); }
function redirect(string $url): void {header('Location: '.$url);exit;}
