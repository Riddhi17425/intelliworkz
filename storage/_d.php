<?php
$apply = getenv('APPLY') === '1';
$root = public_path('uploads/videos');
$files = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $f) {
  if ($f->isFile()) $files[] = str_replace('\\','/',$f->getPathname());
}
$rel = fn($p) => 'uploads/videos/'.ltrim(substr($p, strlen(str_replace('\\','/',$root))), '/');
$groups = [];
foreach ($files as $p) $groups[md5_file($p)][] = $rel($p);

// everything in the database that can hold a path
$blobs = [];
foreach (App\Models\ContentList::all() as $l) $blobs['L'.$l->id] = $l->getRawOriginal('items');
foreach (App\Models\ContentField::all() as $f) $blobs['F'.$f->id] = (string) $f->value;
foreach (App\Models\Page::withTrashed()->get() as $p) $blobs['P'.$p->id] = (string) $p->getRawOriginal('content') . (string) $p->getRawOriginal('layout');
$enc = fn($s) => str_replace('/', '\\/', $s);
$uses = function($path) use ($blobs, $enc) { $n = 0; foreach ($blobs as $b) { $n += substr_count($b, $path) + substr_count($b, $enc($path)); } return $n; };

$map = []; $del = [];
foreach ($groups as $h => $paths) {
  if (count($paths) < 2) { if ($uses($paths[0]) === 0) $del[] = $paths[0]; continue; }
  usort($paths, fn($a,$b) => $uses($b) <=> $uses($a));
  $keep = $paths[0];
  foreach (array_slice($paths, 1) as $dup) { $map[$dup] = $keep; $del[] = $dup; }
  if ($uses($keep) === 0) { $del[] = $keep; unset($map[$keep]); }
}
echo "duplicate groups: ".count(array_filter($groups, fn($g)=>count($g)>1)).", redirected: ".count($map).", files to delete: ".count(array_unique($del))."\n";
$freed = 0; foreach (array_unique($del) as $d) $freed += filesize(public_path($d));
echo "space freed: ".round($freed/1048576,1)." MB\n";
foreach ($del as $d) echo ($uses($d)?'[used] ':'[unused] ').$d.(isset($map[$d])?" -> ".$map[$d]:'')."\n";
if (!$apply) { echo "(dry run)\n"; return; }

$rewrite = fn($s) => str_replace(array_merge(array_map($enc, array_keys($map)), array_keys($map)), array_merge(array_map($enc, array_values($map)), array_values($map)), $s);
foreach (App\Models\ContentList::all() as $l) { $raw = $l->getRawOriginal('items'); $new = $rewrite($raw); if ($new !== $raw) { App\Models\ContentList::where('id',$l->id)->update(['items'=>$new]); } }
foreach (App\Models\ContentField::all() as $f) { $new = $rewrite((string)$f->value); if ($new !== (string)$f->value) App\Models\ContentField::where('id',$f->id)->update(['value'=>$new]); }
foreach (App\Models\Page::withTrashed()->get() as $p) { foreach (['content','layout'] as $col) { $raw = (string)$p->getRawOriginal($col); $new = $rewrite($raw); if ($new !== $raw) App\Models\Page::withTrashed()->where('id',$p->id)->update([$col=>$new]); } }
foreach (array_unique($del) as $d) { @unlink(public_path($d)); }
echo "applied\n";
