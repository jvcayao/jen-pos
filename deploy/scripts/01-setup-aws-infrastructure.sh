#!/bin/bash
set -e

# ============================================================
# AWS Infrastructure Setup Script for Sunbites POS
# Uses AWS Free Tier: EC2 t2.micro + RDS db.t2.micro
# ============================================================

# Configuration
AWS_PROFILE="jhersonn-sunbite-admin"
AWS_REGION="us-east-1"
PROJECT_NAME="sunbites-pos"
KEY_NAME="sunbites-pos-key"
SECURITY_GROUP_NAME="sunbites-pos-sg"
DB_INSTANCE_IDENTIFIER="sunbites-pos-db"
DB_NAME="sunbites_pos"
DB_USERNAME="sunbites_admin"
DB_PASSWORD="SunbitesSecure2024!" # Change this!

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Setting up AWS Infrastructure${NC}"
echo -e "${GREEN}========================================${NC}"

# Export AWS profile
export AWS_PROFILE=$AWS_PROFILE
export AWS_DEFAULT_REGION=$AWS_REGION

# 1. Create Key Pair (if not exists)
echo -e "\n${YELLOW}[1/7] Creating EC2 Key Pair...${NC}"
if aws ec2 describe-key-pairs --key-names $KEY_NAME 2>/dev/null; then
    echo "Key pair '$KEY_NAME' already exists"
else
    aws ec2 create-key-pair \
        --key-name $KEY_NAME \
        --query 'KeyMaterial' \
        --output text > ~/.ssh/${KEY_NAME}.pem
    chmod 400 ~/.ssh/${KEY_NAME}.pem
    echo -e "${GREEN}Key pair created and saved to ~/.ssh/${KEY_NAME}.pem${NC}"
fi

# 2. Get Default VPC
echo -e "\n${YELLOW}[2/7] Getting Default VPC...${NC}"
VPC_ID=$(aws ec2 describe-vpcs \
    --filters "Name=isDefault,Values=true" \
    --query 'Vpcs[0].VpcId' \
    --output text)
echo "Default VPC: $VPC_ID"

# 3. Create Security Group
echo -e "\n${YELLOW}[3/7] Creating Security Group...${NC}"
SG_ID=$(aws ec2 describe-security-groups \
    --filters "Name=group-name,Values=$SECURITY_GROUP_NAME" \
    --query 'SecurityGroups[0].GroupId' \
    --output text 2>/dev/null || echo "None")

if [ "$SG_ID" == "None" ] || [ -z "$SG_ID" ]; then
    SG_ID=$(aws ec2 create-security-group \
        --group-name $SECURITY_GROUP_NAME \
        --description "Security group for Sunbites POS" \
        --vpc-id $VPC_ID \
        --query 'GroupId' \
        --output text)

    # Add inbound rules
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 22 --cidr 0.0.0.0/0    # SSH
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 80 --cidr 0.0.0.0/0    # HTTP
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 443 --cidr 0.0.0.0/0   # HTTPS
    aws ec2 authorize-security-group-ingress --group-id $SG_ID --protocol tcp --port 3306 --source-group $SG_ID  # MySQL internal
    echo -e "${GREEN}Security group created: $SG_ID${NC}"
else
    echo "Security group already exists: $SG_ID"
fi

# 4. Get Latest Ubuntu 22.04 AMI
echo -e "\n${YELLOW}[4/7] Getting Latest Ubuntu 22.04 AMI...${NC}"
AMI_ID=$(aws ec2 describe-images \
    --owners 099720109477 \
    --filters "Name=name,Values=ubuntu/images/hvm-ssd/ubuntu-jammy-22.04-amd64-server-*" \
              "Name=state,Values=available" \
    --query 'sort_by(Images, &CreationDate)[-1].ImageId' \
    --output text)
echo "AMI ID: $AMI_ID"

# 5. Create EC2 Instance
echo -e "\n${YELLOW}[5/7] Creating EC2 Instance (t2.micro - Free Tier)...${NC}"
INSTANCE_ID=$(aws ec2 describe-instances \
    --filters "Name=tag:Name,Values=$PROJECT_NAME" "Name=instance-state-name,Values=running,pending,stopped" \
    --query 'Reservations[0].Instances[0].InstanceId' \
    --output text 2>/dev/null || echo "None")

if [ "$INSTANCE_ID" == "None" ] || [ -z "$INSTANCE_ID" ]; then
    INSTANCE_ID=$(aws ec2 run-instances \
        --image-id $AMI_ID \
        --instance-type t2.micro \
        --key-name $KEY_NAME \
        --security-group-ids $SG_ID \
        --block-device-mappings '[{"DeviceName":"/dev/sda1","Ebs":{"VolumeSize":20,"VolumeType":"gp2"}}]' \
        --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=$PROJECT_NAME}]" \
        --query 'Instances[0].InstanceId' \
        --output text)

    echo "Waiting for instance to be running..."
    aws ec2 wait instance-running --instance-ids $INSTANCE_ID
    echo -e "${GREEN}EC2 Instance created: $INSTANCE_ID${NC}"
else
    echo "EC2 Instance already exists: $INSTANCE_ID"
fi

# 6. Allocate and Associate Elastic IP
echo -e "\n${YELLOW}[6/7] Setting up Elastic IP...${NC}"
EXISTING_EIP=$(aws ec2 describe-addresses \
    --filters "Name=tag:Name,Values=$PROJECT_NAME-eip" \
    --query 'Addresses[0].AllocationId' \
    --output text 2>/dev/null || echo "None")

if [ "$EXISTING_EIP" == "None" ] || [ -z "$EXISTING_EIP" ]; then
    ALLOCATION_ID=$(aws ec2 allocate-address \
        --domain vpc \
        --query 'AllocationId' \
        --output text)

    aws ec2 create-tags --resources $ALLOCATION_ID --tags Key=Name,Value=$PROJECT_NAME-eip

    aws ec2 associate-address \
        --instance-id $INSTANCE_ID \
        --allocation-id $ALLOCATION_ID

    PUBLIC_IP=$(aws ec2 describe-addresses \
        --allocation-ids $ALLOCATION_ID \
        --query 'Addresses[0].PublicIp' \
        --output text)
    echo -e "${GREEN}Elastic IP allocated and associated: $PUBLIC_IP${NC}"
else
    PUBLIC_IP=$(aws ec2 describe-addresses \
        --allocation-ids $EXISTING_EIP \
        --query 'Addresses[0].PublicIp' \
        --output text)
    echo "Elastic IP already exists: $PUBLIC_IP"
fi

# 7. Create RDS MySQL Instance
echo -e "\n${YELLOW}[7/7] Creating RDS MySQL Instance (db.t2.micro - Free Tier)...${NC}"
RDS_EXISTS=$(aws rds describe-db-instances \
    --db-instance-identifier $DB_INSTANCE_IDENTIFIER \
    --query 'DBInstances[0].DBInstanceIdentifier' \
    --output text 2>/dev/null || echo "None")

if [ "$RDS_EXISTS" == "None" ]; then
    # Create DB Subnet Group using default subnets
    SUBNET_IDS=$(aws ec2 describe-subnets \
        --filters "Name=vpc-id,Values=$VPC_ID" \
        --query 'Subnets[*].SubnetId' \
        --output text | tr '\t' ',')

    aws rds create-db-subnet-group \
        --db-subnet-group-name $PROJECT_NAME-subnet-group \
        --db-subnet-group-description "Subnet group for Sunbites POS" \
        --subnet-ids ${SUBNET_IDS//,/ } 2>/dev/null || true

    aws rds create-db-instance \
        --db-instance-identifier $DB_INSTANCE_IDENTIFIER \
        --db-instance-class db.t2.micro \
        --engine mysql \
        --engine-version "8.0" \
        --master-username $DB_USERNAME \
        --master-user-password $DB_PASSWORD \
        --allocated-storage 20 \
        --vpc-security-group-ids $SG_ID \
        --db-subnet-group-name $PROJECT_NAME-subnet-group \
        --db-name $DB_NAME \
        --backup-retention-period 0 \
        --no-multi-az \
        --no-auto-minor-version-upgrade \
        --publicly-accessible

    echo "Waiting for RDS instance to be available (this may take 5-10 minutes)..."
    aws rds wait db-instance-available --db-instance-identifier $DB_INSTANCE_IDENTIFIER
    echo -e "${GREEN}RDS Instance created${NC}"
else
    echo "RDS Instance already exists: $DB_INSTANCE_IDENTIFIER"
fi

# Get RDS Endpoint
RDS_ENDPOINT=$(aws rds describe-db-instances \
    --db-instance-identifier $DB_INSTANCE_IDENTIFIER \
    --query 'DBInstances[0].Endpoint.Address' \
    --output text)

# Summary
echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}Infrastructure Setup Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "EC2 Instance ID: ${YELLOW}$INSTANCE_ID${NC}"
echo -e "EC2 Public IP:   ${YELLOW}$PUBLIC_IP${NC}"
echo -e "RDS Endpoint:    ${YELLOW}$RDS_ENDPOINT${NC}"
echo -e "RDS Database:    ${YELLOW}$DB_NAME${NC}"
echo -e "RDS Username:    ${YELLOW}$DB_USERNAME${NC}"
echo -e "\nSSH Command:"
echo -e "${YELLOW}ssh -i ~/.ssh/${KEY_NAME}.pem ubuntu@$PUBLIC_IP${NC}"
echo -e "\n${RED}IMPORTANT: Save these values for your .env file!${NC}"

# Save outputs to file
cat > deploy/aws-outputs.txt << EOF
EC2_INSTANCE_ID=$INSTANCE_ID
EC2_PUBLIC_IP=$PUBLIC_IP
RDS_ENDPOINT=$RDS_ENDPOINT
RDS_DATABASE=$DB_NAME
RDS_USERNAME=$DB_USERNAME
RDS_PASSWORD=$DB_PASSWORD
SSH_KEY_PATH=~/.ssh/${KEY_NAME}.pem
EOF

echo -e "\n${GREEN}Outputs saved to deploy/aws-outputs.txt${NC}"
