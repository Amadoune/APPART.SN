"""Campaign identity only: never starts a builder. Creation is exclusive, without overwrite."""
import argparse, datetime, hashlib, json, os, pathlib

def create(path, campaign_id, build_sha, source_base_sha, tag):
    # Acquire exclusive ownership before reading the clock; an existing campaign cannot be regenerated.
    with path.open('x', encoding='utf-8', newline='\n') as stream:
        record = dict(schema='appart.packaging-campaign.v1', campaignId=campaign_id,
                      buildDateUtc=datetime.datetime.now(datetime.timezone.utc).strftime('%Y-%m-%dT%H:%M:%SZ'),
                      BUILD_SHA=build_sha, SOURCE_BASE_SHA=source_base_sha, CANDIDATE_TAG=tag)
        stream.write(json.dumps(record, indent=2)+'\n')
        stream.flush()
        os.fsync(stream.fileno())
    path.chmod(0o444)
    return hashlib.sha256(path.read_bytes()).hexdigest()

def context(path, expected_digest):
    data = path.read_bytes()
    if hashlib.sha256(data).hexdigest() != expected_digest:
        raise ValueError('CAMPAIGN_RECORD_INTEGRITY')
    record = json.loads(data)
    return dict(PACKAGING_CAMPAIGN_RECORD=str(path.resolve()), PACKAGING_CAMPAIGN_SHA256=expected_digest,
                PACKAGING_CAMPAIGN_ID=record['campaignId'], PACKAGING_CAMPAIGN_BUILDDATEUTC=record['buildDateUtc'],
                **{k:record[k] for k in ['BUILD_SHA','SOURCE_BASE_SHA','CANDIDATE_TAG']})

if __name__ == '__main__':
    parser=argparse.ArgumentParser()
    commands=parser.add_subparsers(dest='command',required=True)
    c=commands.add_parser('create')
    c.add_argument('path',type=pathlib.Path)
    for name in ['campaign-id','build-sha','source-base-sha','tag']: c.add_argument('--'+name,required=True)
    e=commands.add_parser('context');e.add_argument('path',type=pathlib.Path);e.add_argument('--sha256',required=True)
    args=parser.parse_args()
    if args.command=='create': print(create(args.path,args.campaign_id,args.build_sha,args.source_base_sha,args.tag))
    else: print(json.dumps(context(args.path,args.sha256),indent=2))
